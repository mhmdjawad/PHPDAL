<?php
class DAL{
    public static function getDefaultConnection(){
        $DBOCFG= [
            "host"=>"localhost",
            "database"=>"pdemia_tut",
            "user"=>"root",
            "password"=>""
        ];
        return self::getConnection($DBOCFG);
    }
    public static function getConnection($DBOCFG){
        $db = $DBOCFG['database'];
        $hs = $DBOCFG['host'];
        $us = $DBOCFG['user'];
        $ps = $DBOCFG['password'];
        $DSN = "mysql:host=$hs;dbname=$db";
		$c = new PDO($DSN,$us,$ps,null);
		$c->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        return $c;
    }
    public static function call_sp($q,$p = [],$c = null){
        if ($c == null) $c = self::getDefaultConnection();
        $s = $c->prepare($q);
        foreach($p as $a=>$b){
			$s->bindParam(":".$p[$a]['k'],$p[$a]['v']);
        }
        try{
            if($s->execute()){
                return $s->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        catch(Exception $e){
            p($e->getMessage());
            p($c->errorInfo());
            return;
        }
    }
    public static function execute_query($q,$p = [],$c=null){
        if ($c == null) $c = self::getDefaultConnection();
        $s = $c->prepare($q);
        foreach($p as $a=>$b){
            $s->bindParam(":".$p[$a]['k'],$p[$a]['v']);
        }
        $r =  $s->execute();
        if(strpos($q,"INSERT") > -1 && $r > 0){
            return $c->lastInsertId();
        }
        else return $r;
    }
    public static function getTables($c = null){
        $d = self::call_sp('SHOW TABLES;');
		if(count($d) == 0){ return []; }
		$t = [];
		foreach($d[0] as $a=>$b){
			$k = $a;
		}
		for($i =0; $i<count($d);$i++){
			$t[count($t)] = $d[$i][$k];
		}
		return $t;
    }
    public static function table_exist($table){
        $t = trim($table);
        $t = strtolower($t);
        $tables = self::getTables();
        if(in_array($t,$tables)) 
            return true;
        else
            return false;
    }
    public static function TableFields($t,$c=null){
        $d = self::call_sp("DESCRIBE $t");
        $f = [];
        for($i = 0 ; $i < count($d);$i++){
            array_push($f,$d[$i]['Field']);
        }
        return $f;
    }
    public static function insert($table,$data,$c=null,$force = true,$trace = true){
        if ($c == null) $c = self::getDefaultConnection();
        if(count($data)==0) return -1;
        if(self::table_exist($table) == false ) return -2;
        $ks = array();
        foreach($data as $k=>$v){
            array_push($ks,$k);
        }
        $q = "INSERT INTO $table (".implode( ",", $ks).") VALUES(:". implode( ",:", $ks) .");";
        $s = $c->prepare($q);
        foreach($data as $a=>$b){
			$s->bindParam(":".$a,$data[$a]);
        }
        try{
            if($s->execute()){
                return $c->lastInsertId();
            }
        }
        catch(Exception $e){
            p($e->getMessage());
            p($c->errorInfo());
            return -1;
        }
    }
    public static function update($table,$data,$updatekey,$updatekeyvalue,$c=null){
        if ($c == null) $c = self::getDefaultConnection();
        if(count($data)==0) return -1;
        if(self::table_exist($table) == false ) return -1;
        $ks = array();
        $pqs = array();
        foreach($data as $k=>$v){
            array_push($pqs,$k."=:".$k);
            array_push($ks,$k);
        }
        $q = "UPDATE $table SET ".implode( ",", $pqs)." WHERE $updatekey=:$updatekey";
        $s = $c->prepare($q);
        foreach($data as $a=>$b){
            $s->bindParam(":".$a,$data[$a]);
        }
        $s->bindParam(":".$updatekey,$updatekeyvalue);
        try{
            return $s->execute();
        }
        catch(Exception $e){
            p($e->getMessage());
            p($c->errorInfo());
            return -1;
        }
    }
    public static function criteriaUpdate($table,$data,$critiria,$glue, $c=null){
        if ($c == null) $c = self::getDefaultConnection();
        if(count($data)==0) return -1;
        
        $glue = strtolower($glue);
        if($glue == null) $glue = "and"; // default glue
        if($glue != "and" && $glue != "or") $glue = "and"; // make sure glue is something valid
        

        if(self::table_exist($table) == false ) return -1;
        
        $ks = array();
        $pqs = array();
        
        $critiriaLine = array();
        $fs = self::TableFields($t); //to make sure no 
        
        foreach($critiria as $k=>$v){
            if(!in_array($k,$fs)) return -1;        //validate the column in table!
            array_push($critiriaLine,$k."=:".$k);
        }

        foreach($data as $k=>$v){
            array_push($pqs,$k."=:".$k);
            array_push($ks,$k);
        }
        $q = "UPDATE $table SET ".implode( ",", $pqs)." WHERE ".implode( " $glue ", $critiriaLine)."";
        $s = $c->prepare($q);
        foreach($data as $a=>$b){
            $s->bindParam(":".$a,$data[$a]);
        }
        foreach($critiria as $k=>$v){
            $s->bindParam(":".$k,$critiria[$a]);
        }
        try{
            return $s->execute();
        }
        catch(Exception $e){
            p($e->getMessage());
            p($c->errorInfo());
            return -1;
        }
    }
    public static function delete($t,$key,$val,$c=null){
        if ($c == null) $c = self::getDefaultConnection();
        if(self::table_exist($t) == false ) return -1;
        $fs = self::TableFields($t);
        if(!in_array($key,$fs)) return -1;
        try{
            return self::execute_query("DELETE FROM $t WHERE `".$key."` = :i",[
                ["k"=>"i","v"=>$val]
            ]);
        }
        catch(PDOException $e){
            if(ADMIN) {p($e->getFile() . " " . $e->getLine() . " " . $e->getMessage() );}
            else  p('Please Contact Admin');
		}catch(Error $e){
			if(ADMIN) {p($e->getFile() . $e->getLine() . $e->getMessage() );}
            else  p('Please Contact Admin');
		}
    }
    public static function criteriaDelete($t,$critiria,$glue,$c=null){
        if ($c == null) $c = self::getDefaultConnection();
        if(self::table_exist($t) == false ) return -1;
        $fs = self::TableFields($t);
        
        $glue = strtolower($glue);
        if($gule == null) $glue = "and"; // default glue
        if($glue != "and" && $glue != "or") $glue = "and"; // make sure glue is something vali

        $critiriaLine = array();
        $fs = self::TableFields($t); //to make sure no 
        
        foreach($critiria as $k=>$v){
            if(!in_array($k,$fs)) return -1;        //validate the column in table!
            array_push($critiriaLine,$k."=:".$k);
        }

        $q = "DELETE FROM $t WHERE ".implode( " $glue ", $critiriaLine)."";
        $s = $c->prepare($q);
        foreach($critiria as $k=>$v){
            $s->bindParam(":".$k,$critiria[$a]);
        }
        try{
            return $s->execute();
        }
        catch(PDOException $e){
            if(ADMIN) {p($e->getFile() . " " . $e->getLine() . " " . $e->getMessage() );}
            else  p('Please Contact Admin');
		}catch(Error $e){
			if(ADMIN) {p($e->getFile() . $e->getLine() . $e->getMessage() );}
            else  p('Please Contact Admin');
		}
    }
    //ADMIN PANEL GENERIC FUNCTIONS
    public static function genViewTable($t,$op = []){
        $d = self::call_sp(self::getViewQuery($t));
        $hideCols = [];
        if(count($d) == 0) return "EMPTY";
        $html = '<div class="container-fluid" id="TblCtrl'.time().'" >';
        $html .= '<div id="MsgBox"></div><table id="DataT'.time().'" class="table" >';
        $html .= '<thead>';
        foreach($d[key($d)] as $k=>$v){
            if(in_array($k,$hideCols)) continue;
            $title = $k;
            $title = str_replace("_"," ",$title);
            $html .= '<th class="tableHeader" >' .$title. '</th>';
        }
        if(count($op) > 0 ){
            $html .= '<th class="tableHeader" ><i class="fa fa-cog"></i> </th>';
        }
        $html .= '</thead>';
        $html .= '<tbody>';
        for($i = 0 ; $i < count($d) ; $i++){
            $html .= '<tr>';
            foreach($d[$i] as $c=>$v){
                if(in_array($c,$hideCols)) continue;
                if($c == "cv"){
                    $html .= '<td> <a download="'.$d[$i]['first_name'].'.pdf" href="'.SELF_DIR."Assets/cvs/" . $v . '">Download CV </a></td>';
                }
                else{
                    $html .= '<td>' . $v . '</td>';
                }
            } 
            if(count($op) > 0 ){
                $html .= '<td>';
                if(in_array("edit",$op)){
                    $e = 'TblCtrl'.time();
                    $c = "profile/DBEdit/$t/".key($d[$i]).'/'.$d[$i][key($d[$i])];
                    $html .= '<button type="button" class="btn btn-sm btn-success" onclick="SYS.LoadXHR(\''.$e.'\',\''.$c.'\');" ><i class="fa fa-edit"></i></button>';
                }
                if(in_array("delete",$op)){
                    $e = 'TblCtrl'.time();
                    $c = "profile/DBDelete/$t/".key($d[$i]).'/'.$d[$i][key($d[$i])];
                    $html .= '<button type="button" class="btn btn-sm btn-danger" onclick="SYS.LoadXHR(\'MsgBox\',\''.$c.'\');" ><i class="fa fa-trash"></i></button>';
                }
                $html .= '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<script> $("#DataT'.time().'").DataTable();</script>';
        $html .= '</div>';
        return $html;
    }
    public static function getEditTable($t,$k,$v){
        $d = self::call_sp("select * from $t where $k = :v",[
            ["k"=>"v","v"=>$v]
        ]);
        if(count($d) == 0) die("not found");
        $d = $d[0];
        return self::getFormForTable($t,$d);
    }
    public static function getFormForTable($t,$d = null){
        $html = '<form action="" method="post" class="DALForm">';
        $fs = self::call_sp("describe $t");
        $html .= '<input type="hidden" name="key" value="profile/DBSave" >';
        $html .= '<input type="hidden" name="table" value="'.$t.'" >';
        
        foreach($fs as $fld){
            $F = $fld['Field'];
            if($F == "id"){
                if(!isset($d[$F])) continue;
                $html .= '<input type="hidden" name="'.$F.'" value="'.$d[$F].'" class="form-control">';
            }
            elseif($F == "pw"){
                $html .= '<div class="form-group row">
                    <label class="col-sm-2 col-form-label">'.$F.'</label>
                    <div class="col-sm-10"><input type="password" name="'.$F.'" value="'.( (isset($d[$F])) ? $d[$F] : '' ).'" class="form-control"></div>
                </div>';
            }
            elseif($F == ""){
                $html .= '<div class="form-group row">
                    <label class="col-sm-2 col-form-label">'.$F.'</label>
                    <div class="col-sm-10"><input type="text" name="'.$F.'" value="'.( (isset($d[$F])) ? $d[$F] : '' ).'" class="form-control"></div>
                </div>';
            }
            elseif(strpos($F,"_fk") > -1){
                $html .= '<div class="form-group row">
                    <label class="col-sm-2 col-form-label">'.$F.'</label>
                    <div class="col-sm-10">
                    <select type="text" name="'.$F.'" class="form-control">';
                $fktable = substr($F,0,strpos($F,"_fk"));
                $fks = self::call_sp("select id,name from $fktable");
                foreach($fks as $fk){
                    $sl = "selected=selected";
                    if(isset($d[$F]) && $fk['id'] == $d[$F] ) $sl = "selected=selected";
                    else $sl = "";
                    $html .= '<option value="'.$fk['id'].'" '. $sl .' >'.$fk['name'].'</option>';
                }
                $html .= '</select></div></div>';
            }
            else{
                $html .= '<div class="form-group row">
                    <label class="col-sm-2 col-form-label">'.$F.'</label>
                    <div class="col-sm-10"><input type="text" name="'.$F.'" value="'.( (isset($d[$F])) ? $d[$F] : '' ).'" class="form-control"></div>
                </div>';
            }
        }
        $html .= '<button to="CT'.time().'" type="button" class="btn btn-lg btn-success formSubmitter" onclick="SYS.XHRForm(this);" ><i class="fa fa-save"></i></button>';
        $html .= '<div id="CT'.time().'"></div>';
        return $html;
    }
    public static function getViewQuery($t){
        $q = [];
        if(!isset($q[$t])){
            return "select * from $t";
        }
        else{
            return $q[$t];
        }
    }
    //get Data Access Layer Table
    public static function getDALT($t,$id=null){
        if(method_exists(new DALT,$t)){
            return DALT::$t($id);
        }
        else{
            return DALT::default_view($t,$id);
        }
    }
}
// Data Access Layer Table control : each table can have special query
class DALT{
    public static function default_view($t,$id = null){
        if(! DAL::table_exist($t)){
            return [];
        }
        $addedQ = "";
        $Parms = [];
        if($id != null){
            $addedQ = " and id = :input_id LIMIT 1";
            $Parms = [
                ["k"=>"input_id","v"=>$id]
            ];
        }
        $data_r = DAL::call_sp("SELECT * FROM $t WHERE 1=1 $addedQ",$Parms);
        $data = [];
        foreach($data_r as $d){
            $data[$d['id']] = $d;
        }
        return $data;
    }
}
function p($o,$c="#fff"){
    echo '<pre style="background:'.$c.';padding:5px;" >';print_r($o);echo'</pre>';
}
?>