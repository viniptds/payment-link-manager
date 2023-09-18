<?php

namespace App\Helpers;

use Cielo\API30\Merchant;
use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\CreditCard;

use Cielo\API30\Ecommerce\Request\CieloRequestException;

class CieloGatewayHelper {

    private $environment;
    private $merchant;
    private $sale;
    private $maxInstallments;

    function __construct($transactionId) {
        if(env('CIELO_IS_ENABLED', false) === false) {
            throw new Exception('O gateway não está ativo');
        }
        $this->merchant = new Merchant(env('CIELO_MERCHANT_ID'), env('CIELO_MERCHANT_KEY'));
        $this->sale = new Sale($transactionId);
        $this->environment = env('CIELO_API_ENVIRONMENT', 'sandbox') == 'production' ? Environment::production() : Environment::sandbox();
        $this->maxInstallments = env('CIELO_MAX_INSTALLMENTS', 12);
    }

    function setCustomer($customerName)
    {
        if (!empty($customerName)) {
            $this->sale->customer($customerName);
        }
    }

    function setPayment($value, $installments = 1)
    {
        if (!empty($value) && $installments > 0 && $installments <= $this->maxInstallments) {
            $this->sale->payment($value * 100, $installments);
        }
        
    }

    function makeCreditCardPayment($creditCard)
    {
        $softDescriptor = 'OABCEARA';
        // Crie uma instância de Credit Card utilizando os dados de teste
        // esses dados estão disponíveis no manual de integração.
        $this->sale->getPayment()->setType(Payment::PAYMENTTYPE_CREDITCARD)
        ->setSoftDescriptor($softDescriptor)
        ->creditCard($creditCard['cvv'], $creditCard['brand'])
        ->setExpirationDate($creditCard['expiration_date'])
        ->setCardNumber($creditCard['number'])
        ->setHolder($creditCard['holder']);

        // Crie o pagamento na Cielo
        try {
            // Configure o SDK com seu merchant e o ambiente apropriado para criar a venda
            $sale = (new CieloEcommerce($this->merchant, $this->environment))->createSale($this->sale);

            return $sale;
            // O token gerado pode ser armazenado em banco de dados para vendar futuras
            // $token = $sale->getPayment()->getCreditCard()->getCardToken();
        } catch (CieloRequestException $e) {
            // Em caso de erros de integração, podemos tratar o erro aqui.
            // os códigos de erro estão todos disponíveis no manual de integração.
            $error = $e->getCieloError();
            Log::error($error);
            Log::debug(json_encode($e));
            var_dump($e);
            var_dump($error);
        }
    }


    public function getSale($paymentId) {
        try {
            $sale = (new CieloEcommerce($this->merchant, $this->environment))->getSale($paymentId);return $sale;
        } catch(CieloRequestException $e) {
            $error = $e->getCieloError();
            var_dump($e);
        }
    }
    public function getBinData($bin) {
        try {
            $binData = (new CieloEcommerce($this->merchant, $this->environment))->getBinInformations($bin);
            return $binData;
        } catch(CieloRequestException $e) {
            $error = $e->getCieloError();
        }
    }

    public static function getReturnMessageByCode($responseCode)
    {
        $defaultMessage = 'Falha no pagamento. Tente novamente em alguns instantes';
        $responseMessages = [
            '05' => 'Pagamento não autorizado',
            '57' => 'Cartão expirado',
            '78' => 'Cartão bloqueado',
            '99' => 'Timeout',
            '77' => 'Cartão Cancelado',
            '70' => 'Problemas com o Cartão de Crédito',
        ];

        return $responseMessages[$responseCode] ?? $defaultMessage;
    }

    public static function getAvailableBrands()
    {
        return [
            CreditCard::VISA,
            CreditCard::MASTERCARD,
            CreditCard::AMEX,
            CreditCard::ELO,
        ];
    }
}