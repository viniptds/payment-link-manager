<?php

namespace App\Helpers;

use Cielo\API30\Merchant;
use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\Request\CieloRequestException;

use Illuminate\Support\Facades\Log;

class CieloGatewayHelper {

    private $environment;
    private $merchant;
    private $sale;
    private $maxInstallments;
    private $transactionId;

    public function __construct($transactionId) {
        if(env('CIELO_IS_ENABLED', false) === false) {
            throw new Exception('O gateway não está ativo');
        }
        $this->transactionId = $transactionId;
        $this->merchant = new Merchant(env('CIELO_MERCHANT_ID'), env('CIELO_MERCHANT_KEY'));
        $this->sale = new Sale($transactionId);
        $this->environment = env('CIELO_API_ENVIRONMENT', 'sandbox') == 'production' ? Environment::production() : Environment::sandbox();
        $this->maxInstallments = env('CIELO_MAX_INSTALLMENTS', 12);
    }

    public function setCustomer($customerName, $customerCpf = '')
    {
        if (!empty($customerName)) {
            $this->sale->customer($customerName);
        }

        if (!empty($customerCpf)) {
            $this->sale->getCustomer()->setIdentity($customerCpf);
            $this->sale->getCustomer()->setIdentityType('CPF');
        }
    }

    public function setPayment($value, $installments = 1)
    {
        if (!empty($value) && $installments > 0 && $installments <= $this->maxInstallments) {
            $this->sale->payment($value * 100, $installments);
        }
        
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }

    public function makeCreditCardPayment($creditCard)
    {
        $softDescriptor = 'OABCEARA';

        $this->sale->getPayment()->setType(Payment::PAYMENTTYPE_CREDITCARD)
        ->setCapture(true)
        ->setSoftDescriptor($softDescriptor)
        ->creditCard($creditCard['cvv'], $creditCard['brand'])
        ->setExpirationDate($creditCard['expiration_date'])
        ->setCardNumber($creditCard['number'])
        ->setHolder($creditCard['holder']);

        // Crie o pagamento na Cielo
        try {
            Log::debug('Creating sale on Cielo...');
            Log::debug('Payment ID: ' . $this->getTransactionId());

            $sale = (new CieloEcommerce($this->merchant, $this->environment))->createSale($this->sale);
            Log::debug(json_encode($sale));

            return $sale;
        } catch (CieloRequestException $e) {
            $error = $e->getCieloError();
            $errorResponse = [
                'error' => $error->getMessage(),
                'code' => $error->getCode()
            ];

            Log::error($errorResponse);
            return $errorResponse;
        }
    }

    public function cancelPayment($paymentId, $amount)
    {
        // Aplica o estorno no gateway
        try {
            Log::debug('Voiding sale on Cielo...');
            Log::debug('Payment ID: ' . $paymentId);

            $sale = (new CieloEcommerce($this->merchant, $this->environment))->cancelSale($paymentId, $amount * 100);
            Log::debug(json_encode($sale));

            return $sale;
        } catch (CieloRequestException $e) {
            $error = $e->getCieloError();
            $errorResponse = [
                'error' => $error->getMessage(),
                'code' => $error->getCode()
            ];

            Log::error(json_encode($errorResponse));
            return $errorResponse;
        }
    }

    public function getSale($paymentId) {
        try {
            Log::debug('Getting sale on Cielo...');
            $sale = (new CieloEcommerce($this->merchant, $this->environment))->getSale($paymentId);
            Log::debug(json_encode($sale));
            return $sale;
        } catch(CieloRequestException $e) {
            $error = $e->getCieloError();
            Log::error(json_encode($error));
        }
        return false;
    }

    public function getBinData($bin) {
        try {
            Log::debug('Getting BIN data on Cielo...');
            $binData = (new CieloEcommerce($this->merchant, $this->environment))->getBinInformations($bin);
            Log::debug(json_encode($binData));
            return $binData;
        } catch(CieloRequestException $e) {
            $error = $e->getCieloError();
            Log::error($error);
        }
    }

    public static function creditCardPaymentIsSuccessful($status, $returnCode)
    {
        $success = false;
        $env = env('APP_ENV', 'sandbox');
        
        if ($status == 2) {
            switch($env) {
                case 'production':
                    $success = $returnCode == '00';
                break;
                default:
                    $success = in_array($returnCode, [4, 6]);
                break;
            }
        }

        return $success;
    }

    public static function getCreditCardPaymentReturnMessages($cardBrand = '')
    {
        $env = env('APP_ENV', 'sandbox');

        if ($env == 'production') {
            $responseMessagesDescription = [
                'Contate a Central do seu cartão',
                'Não Autorizada',
                'Senha inválida',
                'Senha inválida',
                'Transação não permitida para o cartão',
                'Transação não permitida para o cartão. Não tente novamente',
                'Verifique os dados do cartão',
                'Verifique os dados do cartão',
                'Verifique os dados do cartão',
                'Suspeita de fraude'
            ];

            switch($cardBrand) {
                case CreditCard::ELO:
                    $responseMessagesCodes = [
                        '05',
                        '51',
                        '55',
                        '55',
                        'x',
                        '57',
                        '14',
                        '56',
                        '63',
                        '59',
                    ];
                break;

                case CreditCard::VISA:
                    $responseMessagesCodes = [
                        '05',
                        '51',
                        '55',
                        '86',
                        'x',
                        '57',
                        '14',
                        '14',
                        'N7',
                        '59',
                    ];
                    break;
    

                case CreditCard::MASTERCARD:
                    $responseMessagesCodes = [
                        '05',
                        '51',
                        '55',
                        '55',
                        '57',
                        'x',
                        '14',
                        '1',
                        '63',
                        '63',
                    ];
                break;

            }

            $responseMessages = array_combine($responseMessagesCodes, $responseMessagesDescription);
        } else {
            $responseMessages = [
                '05' => 'Pagamento não autorizado',
                '57' => 'Cartão expirado',
                '78' => 'Cartão bloqueado',
                '99' => 'Timeout',
                '77' => 'Cartão Cancelado',
                '70' => 'Problemas com o Cartão de Crédito',
            ];
        }

        return $responseMessages;
    }

    public static function creditCardVoidIsSuccessful($status)
    {
        $success = false;
        $env = env('APP_ENV', 'sandbox');
        
        switch($env) {
            case 'production':
                $success = $status == 9;
            break;
            default:
                $success = $status == 10;
            break;
        }

        return $success;
    }

    public static function getCreditCardVoidReturnMessages()
    {
        return [
            '9' => 'Cancelamento realizado com sucesso',
            '72' => 'Saldo do lojista insuficiente para cancelamento da venda',
            '77' => 'Venda não encontrada para cancelamento',
            '100' => 'Forma de pagamento e/ou bandeira não permitem cancelamento',
            '101' => 'Valor de cancelamento solicitado acima do prazo permitido para cancelar'
        ];
    }

    public static function getAvailableBrands()
    {
        return [
            CreditCard::VISA,
            CreditCard::MASTERCARD,
            CreditCard::ELO,
        ];
    }
}