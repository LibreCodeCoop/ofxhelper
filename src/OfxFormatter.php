<?php

declare(strict_types=1);

namespace LibreCode\OfxHandler;

class OfxFormatter
{
    protected $parser;
    protected $transactions = array();

    /**
     * Constructor
     */
    public function __construct($parser)
    {
        $this->parser = $parser;
    }


    /**
     * Adds a new transaction to the OFX file
     *
     */
    public function addTransaction($raw)
    {
        $transaction = array(
            'date'        => null,
            'description' => null,
            'type'        => 'DEBIT',
            'amount'      => 0,
            'balance'     => 0,
        );


        if (isset($raw['date']) && !empty($raw['date'])) {
            $transaction['date'] = $this->parseDate($raw['date']);
        }

        if (isset($raw['description']) && !empty($raw['description'])) {
            $transaction['description'] = $raw['description'];
        }

        if (isset($raw['debit']) && !empty($raw['debit'])) {
            $transaction['type']  = 'DEBIT';
            $transaction['amount'] = 0 - str_replace("$", "", $raw['debit']);
        } elseif (isset($raw['credit']) && !empty($raw['credit'])) {
            $transaction['type']  = 'CREDIT';
            $transaction['amount'] = str_replace("$", "", $raw['credit']);
        }

        if (isset($raw['balance']) && !empty($raw['balance'])) {
            $transaction['balance'] = $raw['balance'];
        }

        $this->transactions[] = $transaction;
    }


    /**
     * Generates the OFX file
     *
     */
    public function generate()
    {
        $transactions = "";
        foreach ($this->transactions as $row) {
            $transactions .= '<STMTTRN>';
            foreach ($row as $name => $value) {
                $transactions .= '<' . strtoupper($name) . '>' . $value . '</' . strtoupper($name) . '>';
            }

            $transactions .= '</STMTTRN>';
        }

        $datetime = date("YmdHis");
        $last     = count($this->transactions) - 1;

        $output = <<<OFX
OFXHEADER:100
DATA:OFXSGML
VERSION:102
SECURITY:NONE
ENCODING:USASCII
CHARSET:1252
COMPRESSION:NONE
OLDFILEUID:NONE
NEWFILEUID:NONE
<OFX>
    <SIGNONMSGSRSV1>
        <SONRS>
            <STATUS>
                <CODE>0</CODE>
                <SEVERITY>INFO</SEVERITY>
            </STATUS>
            <DTSERVER>{$datetime}</DTSERVER>
            <LANGUAGE>POR</LANGUAGE>
            <FI>
              <ORG>Banco Cooperativo do Brasil</ORG>
              <FID>756</FID>
            </FI>
        </SONRS>
    </SIGNONMSGSRSV1>
    <BANKMSGSRSV1>
        <STMTTRNRS>
            <TRNUID>1</TRNUID>
            <STATUS>
                <CODE>0</CODE>
                <SEVERITY>INFO</SEVERITY>
            </STATUS>
            <STMTRS>
                <CURDEF>BRL</CURDEF>
                <BANKACCTFROM>
                    <BANKID>{$this->parser->bankid}</BANKID>
                    <BRANCHID>{$this->parser->banchid}</BRANCHID>
                    <ACCTID>{$this->parser->accid}</ACCTID>
                    <ACCTTYPE>{$this->parser->acctype}</ACCTTYPE>
                </BANKACCTFROM>
                <BANKTRANLIST>
                    <DTSTART>{$this->transactions[$last]['date']}000000
                    <DTEND>{$this->transactions[0]['date']}000000
                    {$transactions}
                </BANKTRANLIST>
                <LEDGERBAL>
                    <BALAMT>{$this->transactions[0]['balance']}</BALAMT>
                    <DTASOF>{$this->transactions[0]['date']}000000</DTASOF>
                </LEDGERBAL>
                <AVAILBAL>
                    <BALAMT>{$this->transactions[0]['balance']}</BALAMT>
                    <DTASOF>{$this->transactions[0]['date']}000000</DTASOF>
                </AVAILBAL>
            </STMTRS>
        </STMTTRNRS>
    </BANKMSGSRSV1>
</OFX>
OFX;

        return $output;
    }
}
