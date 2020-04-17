<?php

namespace App\Service\Translation;

use Symfony\Component\Filesystem\Filesystem;

class TranslationService
{
    private $fileSystem;

    private $rootDir;

    /**
     * TranslationService constructor.
     *
     * @param Filesystem $fileSystem
     * @param string $rootDir
     */
    public function __construct(Filesystem $fileSystem, string $rootDir)
    {
        $this->fileSystem = $fileSystem;
        $this->rootDir = $rootDir;
    }

    /**
     * @param string $locale
     * @param array $domains
     * @return bool
     */
    public function createTranslationFiles(string $locale, array $domains)
    {
        $result = false;

        foreach ($domains as $domain) {
            $result = $this->createTranslationFile($locale, $domain);
        }

        return $result;
    }

    /**
     * @param string $locale
     * @param string $domain
     * @return bool
     */
    private function createTranslationFile(string $locale, string $domain)
    {
        $fileName = "$domain.$locale.db";
        $filePath = "$this->rootDir/translations/$fileName";

        if (!$this->fileSystem->exists($filePath)) {
            $this->fileSystem->touch($filePath);
        }

        return $this->fileSystem->exists($filePath);
    }
}