<?php

namespace App\Model;

use App\Helper\CurrencyConverter;
use Symfony\Component\Validator\Constraints as Assert;

class Coins
{
    /**
     * Default currency
     */
    public const CURRENCY = '£';

    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/([a-oq-z])|(£p)/',
        match: false,
        message: 'Invalid Amount Entered',
    )]
    #[Assert\Type('string')]
    private ?string $amountInput = null;

    #[Assert\Type('int')]
    private int $twoPounds = 0;

    #[Assert\Type('int')]
    private int $onePounds = 0;

    #[Assert\Type('int')]
    private int $fiftyPences = 0;

    #[Assert\Type('int')]
    private int $twentyPences = 0;

    #[Assert\Type('int')]
    private int $twoPences = 0;

    #[Assert\Type('int')]
    private int $onePences = 0;

    /**
     * User input amount
     *
     * @return string|null
     */
    public function getAmountInput(): ?string
    {
        return $this->amountInput;
    }

    /**
     * User set input amount
     *
     * @param string $amountInput
     *
     * @return static
     */
    public function setAmountInput(string $amountInput): static
    {
        $this->amountInput = $amountInput;

        return $this;
    }

    /**
     * Get number of £2 pound coins
     *
     * @return integer
     */
    public function getTwoPounds(): int
    {
        return $this->twoPounds;
    }

    /**
     * Set number of £2 pound coins
     *
     * @param integer $twoPounds
     *
     * @return static
     */
    public function setTwoPounds(int $twoPounds): static
    {
        $this->twoPounds = $twoPounds;

        return $this;
    }

    /**
     * Get number of £2 pound coins
     *
     * @return integer
     */
    public function getOnePounds(): int
    {
        return $this->onePounds;
    }

    /**
     * Set number of £1 pound coins
     *
     * @param integer $onePounds
     *
     * @return static
     */
    public function setOnePounds(int $onePounds): static
    {
        $this->onePounds = $onePounds;

        return $this;
    }

    /**
     * Get number of 50 pence coins
     *
     * @return integer
     */
    public function getFiftyPences(): int
    {
        return $this->fiftyPences;
    }

    /**
     * Set number of 50 pence coins
     *
     * @param integer $fiftyPences
     *
     * @return static
     */
    public function setFiftyPences(int $fiftyPences): static
    {
        $this->fiftyPences = $fiftyPences;

        return $this;
    }

    /**
     * Get number of 20 pence coins
     *
     * @return integer
     */
    public function getTwentyPences(): int
    {
        return $this->twentyPences;
    }

    /**
     * Set number of 20 pence coins
     *
     * @param integer $twentyPences
     *
     * @return static
     */
    public function setTwentyPences(int $twentyPences): static
    {
        $this->twentyPences = $twentyPences;

        return $this;
    }

    /**
     * Get number of 2 pence coins
     *
     * @return integer
     */
    public function getTwoPences(): int
    {
        return $this->twoPences;
    }

    /**
     * Set number of 2 pence coins
     *
     * @param integer $twoPences
     *
     * @return static
     */
    public function setTwoPences(int $twoPences): static
    {
        $this->twoPences = $twoPences;

        return $this;
    }

    /**
     * Get number of 1 pence coins
     *
     * @return integer
     */
    public function getOnePences(): int
    {
        return $this->onePences;
    }

    /**
     * Set number of 1 pence coins
     *
     * @param integer $onePences
     *
     * @return static
     */
    public function setOnePences(int $onePences): static
    {
        $this->onePences = $onePences;

        return $this;
    }

    public function minimumCoinsNeededToEqualAmount(string $amount): void
    {
        $isAmountInPounds = (bool) CurrencyConverter::isAmountInPounds($amount);
        $formattedCurrency = (float) CurrencyConverter::formatCurrency($amount, $isAmountInPounds);

        $currencyInPence = $isAmountInPounds
            ? CurrencyConverter::convertPoundsToPence($formattedCurrency)
            : $formattedCurrency;

        $this->setTwoPounds(intval($currencyInPence / 200));
        $leftOver = $currencyInPence - ($this->getTwoPounds() * 200);

        $this->setOnePounds(intval($leftOver / 100));
        $leftOver = $leftOver - ($this->getOnePounds() * 100);

        $this->setFiftyPences(intval($leftOver / 50));
        $leftOver = $leftOver - ($this->getFiftyPences() * 50);

        $this->setTwentyPences(intval($leftOver / 20));
        $leftOver = $leftOver - ($this->getTwentyPences() * 20);

        $this->setTwoPences(intval($leftOver / 2));
        $leftOver = $leftOver - ($this->getTwoPences() * 2);

        $this->setOnePences(intval($leftOver / 1));
        $leftOver = $leftOver - ($this->getOnePences() * 1);
    }
}
