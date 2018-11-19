<?php

namespace SymphonyPDO\Lib;

class SymphonyPDO extends \PDO
{
    protected $openTransactions = 0;
    const SAVEPOINT_PREFIX = "SymphonyPDO_Transaction_";

    protected function savePointName($transactionNumber=null)
    {
        return sprintf(
            "%s_%d",
            self::SAVEPOINT_PREFIX,
            is_null($transactionNumber)
                ? $this->openTransactions
                : $transactionNumber
        );
    }

    public function beginTransaction()
    {
        if (!$this->openTransactions++) {
            return parent::beginTransaction();
        }
        $this->exec(sprintf(
            "SAVEPOINT `%s`",
            $this->savePointName()
        ));
        return $this->openTransactions >= 0;
    }

    public function commit()
    {
        $this->openTransactions--;
        if ((bool)$this->openTransactions != true) {
            return parent::commit();
        }
        return $this->openTransactions >= 0;
    }

    public function rollback()
    {
        $this->openTransactions--;
        if ((bool)$this->openTransactions == true) {
            $this->exec(sprintf(
                'ROLLBACK TO `%s`',
                $this->savePointName($this->openTransactions + 1)
            ));
            return true;
        }
        return parent::rollback();
    }
}
