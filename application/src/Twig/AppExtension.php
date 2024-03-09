<?php

namespace App\Twig;

use DateTime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    /**
     * Register custom twig functions
     *
     * @return array<int, TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getIpAddress', [$this, 'getIpAddress']),
            new TwigFunction('getCurrentTime', [$this, 'getCurrentTime']),
            new TwigFunction('getFormattedCurentDateAndTime', [$this, 'getFormattedCurentDateAndTime']),
            new TwigFunction('doesFileExist', [$this, 'doesFileExist'])
        ];
    }

    /**
     * Return IP Address
     *
     * @return string
     */
    public function getIpAddress(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    /**
     * Return current timestamp
     *
     * @return string
     */
    public function getCurrentTime(): string
    {
        return date("h:i:sa");
    }

    /**
     * Return formatted date and time string
     *
     * @param DateTime $dateTime
     *
     * @return string
     */
    public function getFormattedCurentDateAndTime(DateTime $dateTime): string
    {
        return date_format($dateTime, "l d F Y H:i:s A");
    }

    /**
     * Check if a given file exists
     *
     * @param string $path
     *
     * @return boolean
     */
    public function doesFileExist(string $path): bool
    {
        return file_exists($_ENV["UPLOAD_DIRECTORY"] . '/' . $path);
    }
}
