<?php

namespace App\Controller;

use App\Form\FormCoinCalculatorType;
use App\Model\Coins;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CoinCalculatorController extends AbstractController
{
    /**
     * Coin Calculator homepage
     *
     * @param Request $request
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function coinCalculator(Request $request, ValidatorInterface $validator): Response
    {
        $coinCalculatorForm = $this->createForm(FormCoinCalculatorType::class, new Coins());
        $coinCalculatorForm->handleRequest($request);

        if ($coinCalculatorForm->isSubmitted() && $coinCalculatorForm->isValid()) {
            $coins = $coinCalculatorForm->getData();

            $coins = $coins->minimumCoinsNeededToEqualAmount($coins->getAmountInput());
        }

        $errors = $validator->validate($coinCalculatorForm);

        return $this->render('pages/coinCalculator.html.twig', [
            'page_name'          => 'Coin Calculator',
            'coinCalculatorForm' => $coinCalculatorForm,
            'errors' => $errors
        ]);
    }
}
