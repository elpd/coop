<?

class Coop_MoneyTransfers extends Awsome_DbTable
{
    const PARTY_TYPE_USER = 1;
    const PARTY_TYPE_COOP = 2;

    public function __construct()
    {
        parent::__construct();
        $this->tableName = "money_transfers";
        $this->editableColumns = array(
            "transferring_party_type",
            "transferring_party_id",
            "receiving_party_type",
            "receiving_party_id",
            "amount",
            "transfer_date",
            "feeder_id",
            "comment"
        );
        $this->primaryColumn = "id";
        $this->orderBy = "id DESC";
    }

    public function addMoneyTransfer($data)
    {
        $newId = $this->add($data);

        return $newId;
    }

    public function getMoneyTransfer($id)
    {
        $sql = "SELECT * FROM money_transfers WHERE id = $id";
        if (!$results = $this->adapter->fetchRow($sql))
        {
            return false;
        }
        return $results;
    }

    public function calc_sumMoneyAmount_fromCoopToUser($user_id, $coop_id, $fromDateStr, $toDateStr) {

        $tr_party_type = $this::PARTY_TYPE_COOP;
        $tr_party_id = $coop_id;

        $rc_party_type = $this::PARTY_TYPE_USER;
        $rc_party_id = $user_id;

        $sql = "select sum(mt.amount) as total
				from money_transfers mt
				where mt.transferring_party_type = '$tr_party_type'
				and mt.transferring_party_id = '$tr_party_id'
				and mt.receiving_party_type = '$rc_party_type'
				and mt.receiving_party_id = '$rc_party_id'
                and mt.transfer_date BETWEEN '$fromDateStr' and '$toDateStr'";

        $row = $this->adapter->fetchRow($sql);

        $total = ($row['total'] != null) ? $row['total'] : 0;
        return $total;
    }

    public function calc_sumMoneyAmount_fromUserToCoop($user_id, $coop_id, $fromDateStr, $toDateStr) {
        $tr_party_type = $this::PARTY_TYPE_USER;
        $tr_party_id = $user_id;

        $rc_party_type = $this::PARTY_TYPE_COOP;
        $rc_party_id = $coop_id;

        $sql = "select sum(mt.amount) as total
				from money_transfers mt
				where mt.transferring_party_type = '$tr_party_type'
				and mt.transferring_party_id = '$tr_party_id'
				and mt.receiving_party_type = '$rc_party_type'
				and mt.receiving_party_id = '$rc_party_id'
				and mt.transfer_date BETWEEN '$fromDateStr' and '$toDateStr'";

        $row = $this->adapter->fetchRow($sql);

        $total = ($row['total'] != null) ? $row['total'] : 0;
        return $total;
    }
}
