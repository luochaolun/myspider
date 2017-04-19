<?php
class DB {
    private $link = null;
    
    public function halt($error, $sql) {
        $message = '<p>SQL: '. htmlentities( $sql ).'<br />';
        $message .= 'Err: ' . $error;
        $message .= '</p>';
		echo $message;
    }
    
    public function __construct() {
		@$this->link = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
		if ($this->link->connect_errno) {
			die($this->link->connect_error); 
        }
		$this->link->set_charset(DB_CHARSET);
    }

    public function __destruct() {
        $this->close();
    }

    /**
     * 执行sql语句
     */
    public function query($sql) {
        $query = $this->link->query($sql);
		if ($query === FALSE) {
			$this->halt($this->link->error, $sql);
		}
		return $query;
    }

	/**
     * 取单行单个字段
     */
	public function get_one($sql) {
        $query = $this->query($sql);
		//echo $query->field_count.'<br>';
		//echo $query->num_rows.'<br>';
        if($query && $query->num_rows > 0){
            $r = $query->fetch_row();
			$query->free();
            return $r[0];
        }
    }
    
    /**
     * 取单行记录
     */
    public function get_row($sql) {
        $query = $this->query($sql);
        if($query && $query->num_rows > 0) {
            $r = $query->fetch_assoc();
			$query->free();
            return $r;
        }
    }
    
    
    /**
     * 取多行记录
     */
    public function get_all($sql) {
        $query = $this->query($sql);
        if($query && $query->num_rows > 0) {
            $results = array();
            while( $r = $query->fetch_assoc()) {
                $results[] = $r;
            }
			$query->free();
			return $results;
        }
    }

	/**
     * 取多行记录
     */
	public function lastid() {
        return $this->link->insert_id;
    }

    /**
     * 关闭
     */
    public function close() {
        if( $this->link ) {
			@$this->link->close();
		}
    }
}