<?php

namespace App\Command;

use App\Entity\FarmPost;
use Doctrine\ORM\EntityManagerInterface;
use Goutte\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class ParseFarmsCommand extends Command
{
    protected static $defaultName = 'app:parse-farms';

    private $em;

    private $crawler;

    private $host;

    private $source = 'https://www.localharvest.org/search.jsp?lat=39.0&lon=-105.5&scale=5&st=6';

    public function __construct(Crawler $crawler, EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->crawler = $crawler;
        $this->em = $entityManager;
        $this->host = 'https://www.localharvest.org';
    }

    protected function configure()
    {
        $this->setDescription('Parse posts from localharvest.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $guzzleClient = new \GuzzleHttp\Client(['timeout' => 180, 'verify' => false]);
        $client = new Client();
        $client->setClient($guzzleClient);

        $source = $client->request('GET', $this->source);
        $pagesNum = $source->filter('.pagination > ul')->children()->count() - 1;
        $savedPostsNum = count($this->em->getRepository(FarmPost::class)->findAll());
        $savedPages = ceil($savedPostsNum / 40);
        $startFromPost = $savedPostsNum ? 40 - ((40 * ceil($savedPostsNum / 40)) - $savedPostsNum) : 0;

        $output->writeln([
            '',
            '<comment>Get posts from: ' . $this->source . '</comment>',
            '====================================================',
            '<info>Number of already saved posts: ' . $savedPostsNum . '</info>',
            '<info>Number of already saved pages: ' . $savedPages . '</info>',
            '<info>Number of pages to save: ' . ($pagesNum - $savedPages) . '</info>',
            '====================================================',
            '',
            '<comment>Start of saving links from all source pages:</comment>',
            ''
        ]);

        $links = [];

        // Get all links to all articles in all pages
        for ($i = 1; $i <= $pagesNum; $i++) {
            if ($i >= $savedPages) {
                $page = $client->request('GET', $this->source . '&p=' . $i);

                foreach ($page->filter('.membercell > .inline > a') as $link) {
                    $links[] = $this->host . $link->getAttribute('href');
                }

                $wait = rand(3, 6);
                $output->writeln('Save links from page: ' . $i . ', and wait ' . $wait . ' seconds!');

                sleep($wait);
            }
        }

        $output->writeln([
            '',
            '====================================================',
            '<comment>Start of posts saving. Number of posts to save: ' . (count($links) - $startFromPost) . '</comment>'
        ]);

        // Get posts by links and save them to the db
        foreach ($links as $key => $link) {
            try {
                if ($key >= $startFromPost) {
                    $farmPost = $this->em->getRepository(FarmPost::class)->findOneBy(['link' => $link]);

                    if (!$farmPost) {
                        $post = $client->request('GET', $link);
                        $sidebar = $post->filter('.sidebar > .panel > div');

                        $contact = $sidebar->filter('.tab')->first()->filter('div > span');
                        $contactName = $contact->first()->count() ? $contact->first()->text() : null;
                        $contactPhone = $contact->first()->count() ? ($contact->eq(1)->count() ? $contact->eq(1)->text() : null) : null;

                        // Sometimes contact name contain phone number instead of name
                        if (!$contactPhone && preg_match('~[0-9]+~', $contactName)) {
                            $contactPhone = $contactName;
                            $contactName = null;
                        }

                        $post = [
                            'link' => $link,
                            'contact' => $contactName,
                            'phone' => $contactPhone,
                            'farmName' => $post->filter('h1')->text(),
                            'street' => null,
                            'city' => null,
                            'state' => null,
                            'zip' => null
                        ];

                        // Get Mailing Address block
                        $addressBlock = $sidebar->filter('.tab')->eq(2)->filter('div');
                        $addressTitle = $addressBlock->filter('h5');

                        // If Mailing Address block doesn't exists, get Location block
                        if (!$addressTitle->count() || $addressTitle->text() != 'Mailing Address') {
                            $addressBlock = $sidebar->filter('.tab')->eq(1)->filter('div');
                            $addressTitle = $addressBlock->filter('h5');
                        }

                        // If block have address info, save address to array
                        if ($addressTitle->count() && ($addressTitle->text() == 'Mailing Address' || $addressTitle->text() == 'Location')) {
                            $addressParts = explode('<br>', $addressBlock->children('div')->html());


                            if (isset($addressParts[1])) {
                                $post['street'] = trim($addressParts[0]);
                            } else {
                                $addressParts[1] = $addressParts[0];
                            }

                            $regionParts = explode(',', trim($addressParts[1]));
                            if (!isset($regionParts[1])) $regionParts = explode(',', trim($addressParts[2]));
                            $post['city'] = $regionParts[0];

                            if (isset($regionParts[1])) {
                                $region = explode(' ', trim($regionParts[1]));
                                $post['state'] = isset($region[0]) && strlen($region[0]) == 2 ? $region[0] : null;
                                $post['zip'] = isset($region[1]) && strlen($region[1]) == 5
                                    ? $region[1]
                                    : (isset($region[0]) && strlen($region[0]) == 5 ? $region[0] : null);
                            }
                        }

                        $this->savePost($post);

                        $wait = rand(3, 6);

                        $output->writeln($key . '. Save post: ' . $post['farmName'] . '(' . $post['link'] . ')' . ' and wait ' . $wait .' seconds!');

                        sleep($wait);
                    }
                }
            } catch (\Exception $exception) {
                $message = 'Exception: ' . $exception->getMessage() . ' Link: ' . $link . ' Post: ' . print_r($post, true);
                throw new \Exception($message);
            }
        }

        $output->writeln('<info>Scrapping successfully completed!</info>');

        return 0;
    }

    /**
     * @param $post
     */
    public function savePost($post)
    {
        $farmPost = new FarmPost();
        $farmPost->setLink($post['link']);
        $farmPost->setTitle($post['farmName']);
        $farmPost->setContact($post['contact']);
        $farmPost->setState($post['state']);
        $farmPost->setCity($post['city']);
        $farmPost->setZip($post['zip']);
        $farmPost->setStreet($post['street']);
        $farmPost->setPhone($post['phone']);

        $this->em->persist($farmPost);
        $this->em->flush();
    }
}