<?php

namespace LibreCode\OfxHandler;

class PagSeguro
{
    private $months = [
        'JAN' => '01',
        'FEV' => '02',
        'MAR' => '03',
        'ABR' => '04',
        'MAI' => '05',
        'JUN' => '06',
        'JUL' => '07',
        'AGO' => '08',
        'SET' => '09',
        'OUT' => '10',
        'NOV' => '11',
        'DEZ' => '12'
    ];
    public $org = 'Pagseguro Internet S.A.';
    public $bankid = 290;
    public $banchid = '0001';
    public $acctid;
    public $acctype = 'CHECKING';

    public function __construct($acctid)
    {
        $this->acctid = $acctid;
    }

    public function parseJson($json) {
        $ofx = new OfxFormatterPagSeguro($this);
        $lastDay = count($json->statementCheckingAccount) -1;
        for ($dayCount = $lastDay; $dayCount >= 0; $dayCount--) {
            $day = $json->statementCheckingAccount[$dayCount];
            $end = count($day->statementMovement) -1;
            for ($i = $end; $i >= 0; $i--) {
                $originalTransaction = $day->statementMovement[$i];
                $date = explode(' ', $day->referenceDate);
                $date = \DateTime::createFromFormat('m-d H:i', $this->months[$date[1]] . '-' . $date[0] . ' ' . $originalTransaction->dateTime);
                $transaction = [];
                $transaction['dtposted'] = $date->format('YmdHis') . '[-3:BRT]';
                $transaction['name'] = $originalTransaction->movementDescription;
                $transaction['trntype'] = $originalTransaction->balanceSign === 'NEGATIVE' ? 'DEBIT' : 'CREDIT';
                $transaction['trnamt'] = $this->cleanMoney($originalTransaction->value);
                $transaction['balamt'] = $originalTransaction->balanceSign;
                $transaction['memo'] = $originalTransaction->defaultStatementDescription;
                $transaction['checknum'] = 0;
                $transaction['refnum'] = 0;
                // $transaction['type'] = $originalTransaction->type;
                $transaction['fitid'] = $originalTransaction->checkingAccountOperationId;
                $this->endBalance = $this->cleanMoney($day->endBalance);
                $ofx->addTransaction($transaction);
            }
        }
        return $ofx->generate();
    }

    private function cleanMoney($string): float {
        $string = str_replace('R$ ', '', $string);
        $string = str_replace('.', '', $string);
        $string = str_replace(',', '.', $string);
        $float = (float) $string;
        return $float;
    }
}
