<?php

namespace DEVUtil;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Eloquent\Enumeration\AbstractEnumeration;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class E_APP
 * General static methods and CONSTS
 * @method static |E_APP SHELL_NE_ERROR_CODE
 */
class E_APP extends AbstractEnumeration
{

    const LINUX_OS = 'Linux';
    const AWS_REGION = 'eu-west-1';
    const SHELL_NE_ERROR_CODE = 127;
    const AWS_INI_REGION_KEYNAME = 'region';
    const AWS_INI_ACCESS_KEYNAME = 'aws_access_key_id';
    const AWS_INI_SECRET_KEYNAME = 'aws_secret_access_key';

    /**
     * Initiates a progress bar for the given process
     *
     * @param OutputInterface $output
     * @param Process $process
     */
    public static function startProcessProgressBar(OutputInterface $output, Process $process)
    {
        $progress = new ProgressBar($output);
        $progress->setOverwrite(true);
        $progress->setFormatDefinition('custom', "%bar%\n%message%");
        $progress->setFormat('custom');
        $process->setTimeout(null);
        $process->start();
        foreach ($process as $type => $data) {
            if ($process::ERR === $type) {
                $process->stop();
                $progress->setMessage(is_string($data) ? $data : array_pop($data));
                $progress->finish();
                return;
            } else {
                $dataArray = array_filter(explode("\n", $data));
                $progress->setMessage(array_pop($dataArray));
                $progress->advance();
            }
        }
        $progress->setProgress(100);
        $progress->finish();
        $process->stop();
        return;
    }

    public static function bumpVersion($currentVersion)
    {
        $newImageVersion = explode(".", $currentVersion[0]);
        for ($i = count($newImageVersion) - 1; $i > -1; --$i) {
            if (++$newImageVersion[$i] < 10 || !$i) {
                break;
            }
            $newImageVersion[$i] = 0;
        }
        return implode(".", $newImageVersion);
    }

    /**
     * Converts an array to .ini format
     *
     * @param  array $data
     * @param string $output
     *
     * @return null|string
     */
    public static function convertToINI($data, &$output = '')
    {
        if (!is_array($data)) return '';
        $output = '';
        foreach ($data as $k => $v) {
            $output .= "[{$k}]\n";
            foreach ($v as $j => $item) {
                $output .= $j . ' = ' . $item . "\n";
            }
        }
        return $output;
    }

    public static function getConfigFiles()
    {
        $whitelabels = [];
        $finder = new Finder();
        $finder->directories()->in(getcwd() . DS . "protected" . DS . "config");
        foreach ($finder as $dir) {
            if ($dir->getFilename() == 'local') continue;
            $whitelabels[] = $dir->getFilename();
        }
        return $whitelabels;
    }
}
