<?php

declare(strict_types=1);

namespace LibreCode\OfxHandler;

class OfxFormatterPagSeguro
{
    private $parser;
    public function __construct($parser)
    {
        $this->parser = $parser;
    }
    public function addTransaction($raw)
    {
        $this->transactions[] = $raw;
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
              <ORG>{$this->parser->org}</ORG>
              <FID>{$this->parser->bankid}</FID>
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
                    <ACCTID>{$this->parser->acctid}</ACCTID>
                    <ACCTTYPE>{$this->parser->acctype}</ACCTTYPE>
                </BANKACCTFROM>
                <BANKTRANLIST>
                    <DTSTART>{$this->transactions[0]['dtposted']}
                    <DTEND>{$this->transactions[$last]['dtposted']}
                    {$transactions}
                </BANKTRANLIST>
                <LEDGERBAL>
                    <BALAMT>{$this->parser->endBalance}</BALAMT>
                    <DTASOF>{$this->transactions[$last]['dtposted']}</DTASOF>
                </LEDGERBAL>
            </STMTRS>
        </STMTTRNRS>
    </BANKMSGSRSV1>
</OFX>
OFX;

        return $output;
    }
}
