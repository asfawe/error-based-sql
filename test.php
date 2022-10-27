<?php
 if (isset($_GET['view-source'])) {
     show_source(__FILE__);
     exit();
 }
 include("./inc.php"); // database connect, $FLAG.
 ini_set('display_errors',false);

 function err($str){ die("<script>alert(\"$str\");window.location.href='./';</script>"); }
// 에러 함수

 function uniq($data){ return md5($data.uniqid());}
// uniqid() 함수는 마이크로 시간(마이크로초 단위의 현재 시간)을 기반으로 고유 ID를 생성합니다.
// .(점)은 뭐죠? 맞아요 바로 연결해주는거죠 data와 uniqid() 함수의 값을 합쳐주는 겁니다.

 function make_id($id){ return mysql_query("insert into all_user_accounts values (null,'$id','".uniq($id)."','guest@nothing.null',2)");}
// all_user_accounts 테이블안에 있는 어떠한 컬럼에 null,'$id','".uniq($id)."','guest@nothing.null',2)"가 들어가겠네요.

 function counting($id){ return mysql_query("insert into login_count values (null,'$id','".time()."')");}
// 위와 똑같습니다.

 function pw_change($id) { return mysql_query("update all_user_accounts set ps='".uniq($id)."' where user_id='$id'"); }
// all_user_accounts 테이블 안에 있는 ps를 업데이트 하는것 같아요.

 function count_init($id) { return mysql_query("delete from login_count where id='$id'"); }
// 로그인 횟수를 삭제??

 function t_table($id) { return mysql_query("create temporary table t_user as select * from all_user_accounts where user_id='$id'"); };
// 임시 테이블 만들기


 if(empty($_POST['id']) || empty($_POST['pw']) || empty($_POST['type'])){
  err("Parameter Error :: missing");
 }
 // 만약 변수가 비어 있으면 err 출력!!!

 $id=mysql_real_escape_string($_POST['id']);
 $ps=mysql_real_escape_string($_POST['pw']);
 $type=mysql_real_escape_string($_POST['type']);
 $ip=$_SERVER['REMOTE_ADDR'];
 // sql injection을 방어하려고 mysql_real_escape_string를 사용합니다.

 sleep(2); // not Bruteforcing!!

 if($id!=$ip){
  err("SECURITY : u can access with allotted id only");
 }
 // ip같은지 확인

 $row=mysql_fetch_array(mysql_query("select 1 from all_user_accounts where user_id='$id'"));
 if($row[0]!=1){
  if(false === make_id($id)){
   err("DB Error :: create user error");
  }
 }
// 만약 id가 없으면 함수 그대로 id를 생성하는 겁니다.

 
 $row=mysql_fetch_array(mysql_query("select count(*) from login_count where id='$id'"));
 $log_count = (int)$row[0];
 if($log_count >= 4){
  pw_change($id);
  count_init($id);
  err("SECURITY : bruteforcing detected - password is changed");
 }
 // 4번을 틀리면 password를 바꾼다.
 
 t_table($id); // don`t access the other account

 if(preg_match("/all_user_accounts/i",$type)){
  err("SECURITY : don`t access the other account");
 }

 counting($id); // limiting number of query

 if(false === $result=mysql_query("select * from t_user where user_id='$id' and ps='$ps' and type=$type")){
  err("DB Error :: ".mysql_error());
 }

 $row=mysql_fetch_array($result);
 
 if(empty($row['user_id'])){
  err("Login Error :: not found your `user_id` or `password`");
 }

 echo "welcome <b>$id</b> !! (Login count = $log_count)";
 
 $row=mysql_fetch_array(mysql_query("select ps from t_user where user_id='$id' and ps='$ps'"));

 //patched 04.22.2015
 if (empty($ps)) err("DB Error :: data not found..");

 if($row['ps']==$ps){ echo "<h2>wow! FLAG is : ".$FLAG."</h2>"; }

?>