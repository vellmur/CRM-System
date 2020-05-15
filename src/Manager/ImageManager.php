<?php

namespace App\Manager;

use App\Entity\Building\Building;
use App\Entity\Media\Image;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class ImageManager
{
    private $em;

    private $helper;

    private $dataManager;

    private $filterManager;

    private $validator;

    private $projectDir;

    /**
     * ImageManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UploaderHelper $helper
     * @param DataManager $dataManager
     * @param FilterManager $filterManager
     * @param ValidatorInterface $validator
     * @param $projectDir
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UploaderHelper $helper,
        DataManager $dataManager,
        FilterManager $filterManager,
        ValidatorInterface $validator,
        $projectDir
    ) {
        $this->em = $entityManager;
        $this->helper = $helper;
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->validator = $validator;
        $this->projectDir = $projectDir;
    }

    /**
     * @param Building|null $building
     * @return \Doctrine\ORM\Query
     */
    public function getImagesQuery(Building $building = null)
    {
        $query = $this->em->createQueryBuilder()
            ->select('image')
            ->from(Image::class, 'image')
            ->orderBy('image.createdAt', 'desc');

        if (null !== $building) {
            $query->where('image.building = :id')
                ->setParameter('id', $building->getId());
        } else {
            $query->where('image.building is null');
        }

        return $query->getQuery();
    }

    /**
     * @param Building|null $building
     * @param $imagesFiles
     * @throws \Exception
     */
    public function uploadImages(?Building $building, $imagesFiles)
    {
        $this->em->beginTransaction();

        try {
            foreach ($imagesFiles as $imageFile) {
                $this->upload($building, $imageFile);
            }

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }
    }

    /**
     * @param Building|null $building
     * @param UploadedFile $imageFile
     * @return Image|null
     * @throws \Exception
     */
    private function upload(?Building $building, $imageFile)
    {
        $image = $this->em->getRepository(Image::class)->findOneBy([
            'name' => $imageFile->getBuildingOriginalName(),
            'building' => $building === null ? null : $building->getId()
        ]);

        // Remove image from db if file doesnt exists
        if ($image !== null && !file_exists($this->getImagePath($image))) {
            $image = $this->removeImage($image);
        }

        if (null === $image) {
            $image = $this->saveImage($building, $imageFile);

            if (!file_exists($this->getImagePath($image))) {
                throw new \Exception('File wasn`t uploaded');
            }
        }

        return $image;
    }

    /**
     * Resize/crop image in original path folder with LiipImagineBundle
     *
     * @param $originalImagePath
     * @param $filter
     * @return UploadedFile
     */
    public function resizeImage($originalImagePath, $filter)
    {
        $image = $this->dataManager->find($filter,  $originalImagePath);

        # Update filter settings
        $configuration = $this->filterManager->getFilterConfiguration();
        $filterConf = $configuration->get($filter);

        $bytesSize = filesize($originalImagePath);
        $mbSize = $this->bytesToMb($bytesSize);
        $quality = $this->getLevelOfSqueeze($mbSize);
        $filterConf['quality'] = $quality;
        $configuration->set($filter, $filterConf);

        $response = $this->filterManager->applyFilter($image, $filter);
        $resizedImage = $response->getContent();

        $f = fopen($originalImagePath, 'w');
        fwrite($f, $resizedImage);
        fclose($f);

        $pathParts = explode('/', $originalImagePath);
        $imageName = $pathParts[count($pathParts) - 1];

        return new UploadedFile($originalImagePath, $imageName);
    }

    /**
     * @param $bytes
     * @return string
     */
    private function bytesToMb($bytes)
    {
        return number_format($bytes / 1048576, 2);
    }

    /**
     * @param $mbSize
     * @return int
     */
    private function getLevelOfSqueeze($mbSize)
    {
        $quality = 100;

        if ($mbSize <= 1) {
            $quality = 90;
        } elseif ($mbSize <= 2) {
            $quality = 70;
        } elseif ($mbSize <= 8) {
            $quality = 50;
        } elseif ($mbSize <= 16) {
            $quality = 30;
        } elseif ($mbSize <= 20) {
            $quality = 20;
        }

        return $quality;
    }

    /**
     * @param Image $image
     * @return false|string
     */
    private function getPublicPath(Image $image)
    {
        $path = $this->helper->asset($image);

        return substr($path, 1);
    }

    /**
     * Save uploaded image file to server, resize/crop image, save image to database
     *
     * @param Building|null $building
     * @param UploadedFile $imageFile
     * @return Image
     * @throws \Exception
     */
    private function saveImage(?Building $building, $imageFile)
    {
        $image = new Image($building);

        // Upload image to the server with vichUploader
        $image->setImageFile($imageFile);

        // Validate
        $errors = $this->validator->validate($image);

        if (count($errors) > 0) {
            throw new \Exception($errors->get(0)->getMessage());
        }

        $this->em->persist($image);

        // Resize/crop image with liipImagine
        $publicPath = $this->getPublicPath($image);
        $resizedImage = $this->resizeImage($publicPath, 'post_image');

        // Update image info in db
        $image->setSize($resizedImage->getSize());
        $this->em->persist($image);
        $this->em->flush();

        return $image;
    }

    /**
     * @param Image $image
     * @return string
     */
    private function getImagePath(Image $image)
    {
        return $this->projectDir . '/public' . $this->helper->asset($image);
    }

    /**
     * @param Image $image
     * @return null
     */
    public function removeImage(Image $image)
    {
        $this->em->remove($image);
        $this->em->flush();

        return null;
    }

    /**
     * @param Image $image
     * @return string|null
     */
    public function getMediaPublicUrl(Image $image)
    {
        return  $this->helper->asset($image);
    }
}