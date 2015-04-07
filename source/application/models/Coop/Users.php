<?
class Coop_Users extends Awsome_DbTable 
{
	const TABLE_NAME = "users";
	const COOP_ID = "coop_id";
	const USER_ID = "user_id";
	const DELETED = "user_deleted";
	const ACCESS = "user_access";
	const FIRST_NAME = "user_first_name";
	const LAST_NAME = "user_last_name";
	const PASSWORD = "user_password";
	const PHONE = "user_phone";
	const PHONE2 = "user_phone2";
	const EMAIL = "user_email";
	const EMAIL2 = "user_email2";
	const EMAIL3 = "user_email3";
	const EMAIL4 = "user_email4";
	const ADDRESS = "user_address";
	
	const SESSION_NAME = "coop_users";
	
	public function __construct()
	{
		parent::__construct();
		$this->tableName = self::TABLE_NAME;
		$this->editableColumns = array(self::COOP_ID, self::ACCESS, self::FIRST_NAME, self::LAST_NAME, self::PASSWORD, self::PHONE, self::PHONE2,
							self::EMAIL, self::EMAIL2, self::EMAIL3, self::EMAIL4, self::ADDRESS, "user_comments", "user_job");
		$this->nameColumn = self::FIRST_NAME;
		$this->primaryColumn = self::USER_ID;
		$this->deleteColumn = self::DELETED;
		$this->orderBy = self::FIRST_NAME;
	}
		
	public function login($email, $password, $coop)
	{
		$session = new Zend_Session_Namespace(self::SESSION_NAME);
		$sql = $this->adapter->quoteInto("SELECT * FROM users WHERE (user_email = ?", $email);
		$sql .= $this->adapter->quoteInto(" OR user_email2 = ?", $email);
		$sql .= $this->adapter->quoteInto(" OR user_email3 = ?", $email);
		$sql .= $this->adapter->quoteInto(" OR user_email4 = ?)", $email);
		$sql .= $this->adapter->quoteInto(" AND user_password = ?", $password);
		$sql .= $this->adapter->quoteInto(" AND (coop_id = ?", (int)$coop['coop_id']);
		$sql .= " OR user_access = 'SUPER') AND user_deleted = 0";
		$row = $this->adapter->fetchRow($sql);
                
		if (!$row)
		{
			return false;
		}
		else 
		{
			$session = new Zend_Session_Namespace(self::SESSION_NAME);
			$session->user_id = $row['user_id'];
			$session->display_name = htmlspecialchars($row['user_first_name'] . " " . $row['user_last_name']);
			$session->coop = $coop;
			return $this->isLogged();
		}
	}
	
	public function isLogged()
	{
		$session = new Zend_Session_Namespace(self::SESSION_NAME );
		return (isset($session->user_id) && !empty($session->user_id));
	}
	
	public function getLoggedUserID()
	{
		$session = new Zend_Session_Namespace(self::SESSION_NAME );
		return $session->user_id;
	}
	
	public function getLoggedUserCoop()
	{
		$session = new Zend_Session_Namespace(self::SESSION_NAME );
		$coop_session = $session->coop; 
		$coop_id = $coop_session['coop_id'];
		
		$coop_coops = new Coop_Coops();
		$coop = $coop_coops->getCoop($coop_id);
		return $coop; 
	}
	
	public function logout()
	{
		$session = new Zend_Session_Namespace(self::SESSION_NAME );
		unset($session->user_id);		
	}
        
        public function getAllUsersFromAllCoops()
        {
            $sql = "SELECT * FROM users WHERE user_deleted = 0 ORDER BY coop_id, user_first_name";
            if (!$results = $this->adapter->fetchAll($sql))
            {
                    return false;
            }
            return $results;
        }


        public function getAllUsers($coop_id)
	{
		return $this->getUsers("*", $coop_id);
	}

    public function getAllUsersWithDebt($coop_id) {
        $users = $this->getAllUsers($coop_id);
        $newUsers = array();
        foreach ($users as $user) {
           $user_debt = $this->calcDebtFromBeginingTillNow($user['user_id']);
           $user['calc_current_debt'] = $user_debt;
            array_push($newUsers, $user);
        }

        return $newUsers;
    }

	public function getAllUsersFullNameAndID($coop_id)
	{
		return $this->getUsers("user_id, user_first_name, user_last_name", $coop_id);
	}
	
	private function getUsers($fields, $coop_id)
	{
		$sql = "SELECT $fields FROM users WHERE coop_id = " . (int)$coop_id . " AND user_deleted = 0 ORDER BY user_first_name";
		if (!$results = $this->adapter->fetchAll($sql))
		{
			return false;
		}
		return $results;
	}
	
	public function getUserOrFalse($id) {
		$sql = "SELECT * FROM users WHERE user_id = " . (int)$id;
		if (!$result = $this->adapter->fetchRow($sql)) {
			return false;
		} 
		return $results;
	}
	
	public function getUser($id)
	{
		return $this->getOne($id);
	}
	
	public function getUserOrNull($id)
	{
		$sql = $this->adapter->quoteInto("SELECT * FROM users WHERE user_id = ?", $id);
		return $this->adapter->fetchRow($sql);
	}
	
	public function addUser($coop_id, $params)
	{
		$params['coop_id'] = $coop_id;
		return $this->add($params);
	}
	
	public function editUser($id, $params)
	{
		return $this->edit($id, $params);
	}
	
	public function deleteUser($id)
	{
		return $this->delete($id);
	}

    public function calcDebt($id, $fromDateStr, $toDateStr) {

        $moneyTransfers = new Coop_MoneyTransfers();
        $orders = new Coop_Orders();
        $user = $this->getUser($id);

        $user_id = $user['user_id'];
        $coop_id = $user['coop_id'];

        //

        $receiving = $moneyTransfers->calc_sumMoneyAmount_fromCoopToUser(
            $user_id, $coop_id, $fromDateStr, $toDateStr);

        $giving = $moneyTransfers->calc_sumMoneyAmount_fromUserToCoop(
            $user_id, $coop_id, $fromDateStr, $toDateStr);

        $userSumCoopCharges = $orders->calcForUser_sumCharges(
            $user_id, $fromDateStr, $toDateStr);


        $userCurrentDept = $userSumCoopCharges - ($giving - $receiving);

        return $userCurrentDept;
    }

    public function calcDebtFromBegining($id, $toDateStr) {
        $fromDateStr = '2000-01-01';
        return $this->calcDebt($id, $fromDateStr, $toDateStr);
    }

    public function calcDebtFromBeginingTillNow($user_id) {
        $fromDateStr = '2000-01-01';
        $toDateStr = date('Y-m-d H:i:s');
        return $this->calcDebt($user_id, $fromDateStr, $toDateStr);
    }
}