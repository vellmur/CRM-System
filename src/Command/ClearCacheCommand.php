<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class ClearCacheCommand extends Command
{
    protected static $defaultName = 'app:cache:clear';

    private $host;

    private $cacheDir;

    private $env;

    private static $cleanerFile = 'thisispageforclearingofcache.php';

    public function __construct($protocol, $domain, $rootDir, $env)
    {
        parent::__construct();

        $this->host = $protocol . '://' . $domain;
        $this->cacheDir = $rootDir . '/var/cache/' . $env;
        $this->env = $env;
    }

    protected function configure()
    {
        $this->setDescription('Clear OPCache files')
            ->addArgument('warmup', InputArgument::OPTIONAL, 'To warmup cache or not.')
            ->addArgument('subfolder', InputArgument::OPTIONAL, 'Subfolder of cache directory.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['Start clearing:', '']);

        $warmUp = $input->getArgument('warmup') !== null
            ? filter_var($input->getArgument('warmup'), FILTER_VALIDATE_BOOLEAN) : true;

        // Clear OPCache
        $output->writeln(' // Clear OPCache...');
        $url = $this->host . '/' . self::$cleanerFile . '?' . time();
        $postQuery = 'secretKey=noonecanknowthiskeybecauseitssecret48152020';
        $response = $this->curlConnect($url, $postQuery);

        $output->writeln(['', ' // ' . $response, '']);

        // Clear project cache
        $output->writeln(' // Clear project cache...');

        // Add subDir to cache clear in its given
        if ($subDir = $input->getArgument('subfolder')) {
            $subDir = str_replace('/', '', $subDir);
            $this->cacheDir .= '/' . $subDir;

            $result = $this->removeCacheDir();

            $output->writeln(['', ' // ' . $result]);
        } else {
            $command = $this->getApplication()->find('cache:clear');
            $arguments = [
                'command' => 'cache:clear',
                '--env' => $this->env
            ];

            $greetInput = new ArrayInput($arguments);
            $command->run($greetInput, $output);
        }


        // WarmUp cache
        if (true === $warmUp) {
            $command = $this->getApplication()->find('cache:warmup');
            $arguments = [
                'command' => 'cache:warmup',
                '--env' => $this->env
            ];

            $greetInput = new ArrayInput($arguments);
            $command->run($greetInput, $output);
        }

        return 0;
    }

    function checkBool($string){
        $string = strtolower($string);
        return (in_array($string, array("true", "false", "1", "0", "yes", "no"), true));
    }

    /**
     * @param $url
     * @param $post
     * @return bool|string
     */
    private function curlConnect($url, $post)
    {
        $headers = [
            "text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5",
            "Cache-Control: max-age=0",
            "Connection: keep-alive",
            "Keep-Alive: 300",
            "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
            "Accept-Language: en-us,en;q=0.5"
        ];

        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $response = curl_exec($ch);

        curl_close($ch);

        return  str_replace('"', '', $response);
    }

    /**
     * @return string
     */
    private function removeCacheDir()
    {
        $filesystem = new Filesystem();

        try {
            if ($filesystem->exists($this->cacheDir)) {
                $filesystem->remove($this->cacheDir);

                $result = 'Cache was successfully removed';
            } else {
                $result = 'Cache folder wasn`t found';
            }
        } catch (IOExceptionInterface $exception) {
            $result = "An error occurred while removing your cache at " . $exception->getPath() . ' ' . $exception->getMessage();
        }

        return $result;
    }
}