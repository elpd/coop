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

    public function queryTransfersFitForOrder($order_id) {
        $coop_orders = new Coop_Orders();
        $coop_users = new Coop_Users();

        $order = $coop_orders->getOrder($order_id);
        if (!$order) {
            throw new \Exception('operation require existing item: order. id: ' . $order_id);
        }

        $user = $coop_users->getUser($order['user_id']);
        if (!$user) {
            throw new \Exception('operation require existing sub item: user. id: ' . $order['user_id']);
        }

        $tr_party_type = $this::PARTY_TYPE_USER;
        $tr_party_id = $order['user_id'];
        $rc_party_type = $this::PARTY_TYPE_COOP;
        $rc_party_id = $user['coop_id'];

        if ($order['time_of_closing']) {
            $temp_date = $order['time_of_closing'];
            $order_date = date('Y-m-d', strtotime($temp_date));
        } else {
            return false;
        }

        $sql = "select mt.*
				from money_transfers mt
				where cast(mt.transfer_date as date) BETWEEN '$order_date' AND '$order_date'
				and mt.transferring_party_type = '$tr_party_type'
				and mt.transferring_party_id = '$tr_party_id'
				and mt.receiving_party_type = '$rc_party_type'
				and mt.receiving_party_id = '$rc_party_id'";

        $row = $this->adapter->fetchRow($sql);

        return $row;
    }
}
