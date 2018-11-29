<?php

namespace SymphonyPDO\Lib;

class SymphonyPDO extends \PDO
{
    const SAVEPOINT_PREFIX = "SymphonyPDO_Transaction_";

    protected $log = [];
    protected $totalOpenTransactions = 0;

    public function addToLog($m, $includeTrace=false) {
        $trace = $includeTrace
            ? $this->generateCallTrace()
            : NULL
        ;

        $this->log[] = [
            "message" => $m,
            "connectionId" => $this->connectionId(),
            "openTransactions" => $this->totalOpenTransactions,
            "trace" => $trace,
        ];
    }

    public function log() {
        return $this->log;
    }

    public function incrementOpenTransactionCount() {
        $this->totalOpenTransactions++;
    }

    public function decrementOpenTransactionCount() {
        $this->totalOpenTransactions--;
    }

    public function connectionId() {
        return $this->query('SELECT CONNECTION_ID()')->fetchColumn();
    }

    public function isOpenTransactions() {
        return ($this->totalOpenTransactions > 0);
    }

    public function rollbackToSavepoint($savepoint) {
        $sql = sprintf(
            'ROLLBACK TO `%s`',
            $this->savePointName($savepoint)
        );
        try{
            $this->exec($sql);
        } catch(\Exception $ex) {
            $this->addToLog(sprintf(
                "Rollback to '%s' has failed. Returned: %s",
                $this->savePointName($savepoint), $ex->getMessage()
            ));
            throw $ex;
        }
        return true;
    }

    public function createSavepoint() {
        $this->incrementOpenTransactionCount();
        $sql = sprintf(
            "SAVEPOINT `%s`",
            $this->savePointName()
        );
        try{
            $this->exec($sql);
        } catch(\Exception $ex) {
            $this->addToLog(sprintf(
                "Creating savepoint '%s' has failed. Returned: %s",
                $this->savePointName(), $ex->getMessage()
            ));
            throw $ex;
        }
        return true;
    }

    protected function savePointName($transactionNumber=null)
    {
        return sprintf(
            "%s%04d",
            self::SAVEPOINT_PREFIX,
            is_null($transactionNumber)
                ? $this->totalOpenTransactions
                : $transactionNumber
        );
    }

    public function beginTransaction() {
        if(!$this->isOpenTransactions()) {
            $this->incrementOpenTransactionCount();
            return parent::beginTransaction();

        } else {
            $this->createSavepoint();
        }
        return $this->isOpenTransactions();
    }

    public function commit() {
        $this->decrementOpenTransactionCount();
        if(!$this->isOpenTransactions()) {
            return parent::commit();
        }
        return $this->isOpenTransactions();
    }

    public function rollback() {
        $this->decrementOpenTransactionCount();
        if($this->isOpenTransactions()) {
            return $this->rollbackToSavepoint($this->totalOpenTransactions + 1);
        }
        return parent::rollback();
    }
}
