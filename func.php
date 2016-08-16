<?php
if (!defined('wf_php')) {
    header('Location: /');
    exit();
}

include 'core/file.php';
include 'core/smtp.php';
include 'core/alias.php';
include 'core/form.php';
include 'core/mysql.php';
include 'core/access.php';
include 'core/DB_Switcher.php';
include 'core/mailtools.php'; 

function get_file($filename)
{
      $handle = fopen($filename, "r");
      $contents = fread($handle, filesize($filename));
      fclose($handle);
      return $contents;
}

function save_file($file,$info,$flag='w')
{
        $w=fopen($file,$flag);
        fwrite($w,$info);
        fclose($w);
}

function select($name,$table,$var1='id',$var2='name',$class='',$where='',$select='',$first=''){
global $prefixbd;
$where=($where)?"WHERE($where)":'';
$res='<select name="'.$name.'"'.$class.'>';
	if($first) $res.='<option value="">'.$first.'</option>';
$sql=mysql_query("SELECT `$var1`,`$var2` FROM `{$prefixbd}$table`$where")or die(mysql_error());
if(is_array($select))
{
	while($chr=mysql_fetch_array($sql))
	$res.='<option value="'.$chr[$var1].'"'.((in_array($chr[$var1],$select))?' selected':'').'>'.$chr[$var2].'</option>'."\n";
}else{
	while($chr=mysql_fetch_array($sql))
	$res.='<option value="'.$chr[$var1].'"'.(($chr[$var1]==$select)?' selected':'').'>'.$chr[$var2].'</option>'."\n";
}
$res.='</select>';
return $res;
}



function preview( $text, $maxchar = 250,$maxwords = 200) {
$text=strip_tags($text,'<br>');
$sep = '/ /'; $words = preg_split( $sep, $text );
$char = iconv_strlen( $text, 'utf-8' );
if ( count( $words ) > $maxwords )
{ $text = join($sep, array_slice($words, 0, $maxwords)); }
if ( $char > $maxchar ) { $text = iconv_substr( $text, 0, $maxchar, 'utf-8' ).'...'; } return $text; }

function convert($text)
    {
        global $system;
        foreach($system as $p=>$r)
        $text=str_replace('{'.$p.'}',$r,$text);
        return $text;
    }

function convert_lang($text,$lang)
    {
        global $prefixbd;
        $sql=mysql_query("SELECT * FROM `{$prefixbd}lang`");
        while($list=mysql_fetch_assoc($sql))
            $text=str_replace('{#'.$list['cod'].'}',$list[$lang],$text);
        return $text;
    }



//password generator
function generate_password($number=6)
{
$arr = array('a','b','c','d','e','f', 'g','h','i','j','k','l', 'm','n','o','p','r','s', 't','u','v','x','y','z', 'A','B','C','D','E','F', 'G','H','I','J','K','L','M','N','O','P','R','S', 'T','U','V','X','Y','Z','1','2','3','4','5','6','7','8','9','0');
$pass = "";
for($i = 0; $i < $number; $i++)
{
$index = rand(0, count($arr) - 1);
$pass .= $arr[$index];
}  return $pass; }



//Get extention
function getext($file)
{
if(file_exists($file.'.jpg')) $ext='.jpg';
elseif(file_exists($file.'.png')) $ext='.png';
elseif(file_exists($file.'.gif')) $ext='.gif';
return $file.$ext;
}

 

//--Class for simple table form
class form{
#h-input type="hidden"
#i-input type="text"
#t-textarea
#d-input date
#c-input type="checkbox"
#n-th table
#u-custom user
#p-password
#f - file

var $capt;
var $input;
var $name;
var $value;

var $action;
var $method="post";
var $user;
var $buttons;

function create_form(){
$form='<form action="'.$this->action.'" method="'.$this->method.'"'.((strpos($this->input,'f')!==false)?' enctype="multipart/form-data"':'').'>';
$form.="\n<table class=\"form\">\n";
$name=explode(';',$this->name);
$input=explode(';',$this->input);
$capt=explode(';',$this->capt);
$value=$this->value;
//element form
for($i=0;$i<=count($input)-1;$i++)
{
  if($input[$i]=='N') $form.="<tr><th colspan=\"2\">$capt[$i]</th></tr>\n";
  if($input[$i]=='i') $form.="<tr><td>$capt[$i]</td><td><input type=\"text\" name=\"$name[$i]\" value=\"".$value[$name[$i]]."\" /></td></tr>\n";
  if($input[$i]=='t') $form.="<tr><td>$capt[$i]</td><td><textarea name=\"$name[$i]\">".$value[$name[$i]]."</textarea></td></tr>\n";
  if($input[$i]=='c') $form.="<tr><td>$capt[$i]</td><td><input type=\"checkbox\" name=\"$name[$i]\"".(($value[$name[$i]])?" checked":"")."></td></tr>\n";
  if($input[$i]=='u') $form.=$this->user[$i];
  if($input[$i]=='h') $form.="<tr><td>$capt[$i]</td><td><input type=\"hidden\" name=\"$name[$i]\" value=\"".$value[$name[$i]]."\" /></td></tr>\n";
  if($input[$i]=='p') $form.="<tr><td>$capt[$i]</td><td><input type=\"password\" name=\"$name[$i]\" value=\"".$value[$name[$i]]."\" /></td></tr>\n";
  if($input[$i]=='f') $form.="<tr><td>$capt[$i]</td><td><input type=\"file\" name=\"$name[$i]\" value=\"".$value[$name[$i]]."\" /></td></tr>\n";
}

$buttons=($this->buttons)?'':"<input type=\"submit\" value=\"Submit\" /> <input type=\"reset\" value=\"Cancel\" />";
//Buttons
if(!$buttons)
foreach($this->buttons as $b=>$t)
{
if($b=='Y') $buttons.='<input type="submit" value="'.(($t)?$t:'Submit').'" class="button"/>'."\n";
if($b=='R') $buttons.='<input type="reset" value="'.(($t)?$t:'Cancel').'" />'."\n";
if($b=='B') $buttons.='<input type="reset" value="'.(($t)?$t:'Back').'"  class="button" OnClick="url(\''.back().'\')" />'."\n";
if($b=='BB') $buttons.='<input type="reset" value="'.(($t)?$t:'Back').'"  class="button" OnClick="url(\''.back(1).'\')" />'."\n";
}

$form.='<tr><td></td><td>'.$buttons.'</td></tr>';
$form.="</table>\n</form>\n\n";
$this->res=$form;
}
}





function strip_figure($disign){

function systempreg($in){
global $system;
if(isset($system[$in]))
return $system[$in]; else return '{'.$in.'}';}
$disign=preg_replace('/{(.*?)}/ie',"systempreg('\\1')",$disign);

return $disign;
}

function selectuniq($name,$table,$id,$row,$where,$selected,$first='', $idclass='', $tt='', $req=''){
	$uniquearr=array();
	$sql=mysql_query("SELECT `$id`,`$row` FROM `$table` WHERE $where")or die(mysql_error());
	while ($chr=mysql_fetch_row($sql))
	{
		if(!isset($uniquearr[$chr[0]]))$uniquearr[$chr[0]]=$chr[1];
	}

		$res='<select name="'.$name.'" id="'.$idclass.'" title="'.$tt.'" '.$req.'>';
			if($first) $res.='<option value="">'.$first.'</option>';
		foreach ($uniquearr as $i=>$r)
		{
			$res.='<option value="'.$i.'" '.(($selected==$i)?' selected':'').'>'.$r.'</option>';
		}
		$res.='</select>';

	return $res;
}


function selectuniqd($name,$table,$row,$where,$selected='',$format,$first=''){
	global $prefixbd;
	$uniquearr=array();
	$sql=mysql_query("SELECT `$row` FROM `{$prefixbd}$table` WHERE $where")or die(mysql_error());
	while ($chr=mysql_fetch_row($sql))
		if(!isset($uniquearr[date($format,$chr[0])]))$uniquearr[date($format,$chr[0])]=$chr[0];
	$res='<select name="'.$name.'">';
		if($first) $res.='<option value="">'.$first.'</option>';
	foreach ($uniquearr as $r=>$i)
		$res.='<option value="'.$r.'"'.(($selected==$r)?' selected':'').'>'.$r.'</option>';
	$res.='</select>';
return $res;
}



/*conves*/
function highlight($h,$text) {return str_replace($h,'<em class="highlight">'.$h.'</em>',$text);}
function conv_date($text)    {$text=date('d.m.Y',(int)$text); return $text;}//date('d.m.Y',$text);}


//encoding unicode UTF-8 -> win1251
function utf8_win ($s){
$out="";
$c1="";
$byte2=false;
for ($c=0;$c<strlen($s);$c++){
$i=ord($s[$c]);
if ($i<=127) $out.=$s[$c];
if ($byte2){
$new_c2=($c1&3)*64+($i&63);
$new_c1=($c1>>2)&5;
$new_i=$new_c1*256+$new_c2;
if ($new_i==1025){
$out_i=168;
}else{
if ($new_i==1105){
$out_i=184;
}else {
$out_i=$new_i-848;
}
}
$out.=chr($out_i);
$byte2=false;
}
if (($i>>5)==6) {
$c1=$i;
$byte2=true;
}
}
return $out;
}

//Get count sql
function sql_count($table,$where=''){
  global $prefixbd;
$where=($where)?" WHERE $where ":'';
$counts=mysql_result(mysql_query("SELECT COUNT(*) FROM `{$prefixbd}$table`$where"),0,0);
return $counts;
}

function select_to_date($d,$m,$y){
if($d && $m && $y) return strtotime("$d-$m-$y");
if($m && $y) return strtotime("1-$m-$y");
if($y) return strtotime("1-1-$y");
}

function ob_end(){
while (@ob_end_flush());
}

//Class ImG Resize
class cimage{
	var $bg_red=255;
	var $bg_green=255;
	var $bg_blue=255;
	var $jpeg_quality=90;
	var $crop=true;

	function set_bg($red,$green,$blue){
		$this->bg_red=$red;
		$this->bg_green=$green;
		$this->bg_blue=$blue;
	}

	function set_quality_jpeg($quality){
		$this->jpeg_quality=$quality;
	}

	function resizeimg($input,$output,$fw,$fh){
		//$im = @imageCreateFromJpeg($input);

    //Get Image size info
    list($w, $h, $image_type) = getimagesize($input);

    switch ($image_type)
    {
        case 1: $im = imagecreatefromgif($input); break;
        case 2: $im = imagecreatefromjpeg($input);  break;
        case 3: $im = imagecreatefrompng($input); break;
        default: $im = false;  break;
    }


		if ($im){
			$w = imageSX($im);
			$h = imageSY($im);
            if(!$fw) $fw=intval($fh*($w/$h));
            if(!$fh) $fh=intval($fw*($w/$h));
			$new = imagecreatetruecolor($fw, $fh);

			$color = ImageColorAllocate($new, $this->bg_red, $this->bg_green, $this->bg_blue);
			imagefill($new,0,0,$color);



$ratio=$w/$h;
$retcrop=($this->crop)?$ratio<($fw/$fh):$ratio>($fw/$fh);

			if ($retcrop){
				$nw=$fw;
				$nh=$nw/$ratio;
				$ost=intval(($fh-$nh)/2);
				imagecopyresampled($new, $im, 0, $ost, 0, 0, $nw, $nh, $w, $h);
			}else{
				$nh=$fh;
				$nw=$nh*$ratio;
				$ost=intval(($fw-$nw)/2);
				imagecopyresampled($new, $im, $ost, 0, 0, 0, $nw, $nh, $w, $h);
			}
      //if($im==2)
    switch ($image_type)
    {
        case 1: imagegif($new,$output); break;
        case 2: imagejpeg($new,$output,$this->jpeg_quality); break;
        case 3: imagepng($new,$output); break;
    }


			imageDestroy($im);
			imageDestroy($new);
			return 1;
		}else return 0;
	}


}

class page {
    public $recCount = 0;
    public $perp = 10;
    public $page = 1;
    public $sql = "";
    public $sofSql = array(
        "from" => "",
        "where" => "",
    );

    public $pagesLim = 0;
    public $back = "";
    public $next = "";
    public $link = "";
    private $pages = "";

    private function pages()
    {
        if(!$this->page) $this->page = 1;
        $l = (int)$this->perp;
        $i = (int)$this->page;
        $p = $l*($i-1);

        $count_page = mysql_query($this->sql);
        $delen = mysql_num_rows($count_page);

        $this->sql = mysql_query($this->sql." LIMIT $p, $l")or die(mysql_error());
        $pages = '<div class="pages">';

        if($delen > $l)
        {
            $outdelen = ($delen-1)/$l + 1;
            $outcent = (int)$outdelen;
            for($r = 0; $r < $outcent; $r++)
            {
                $t = $r + 1;
                if ($i != $t) $pages .= str_replace('%page%',$t,$this->link);
                else $pages.="<b>$t</b>";
                if($r != $outcent) $pages .= "\n";
            }
        }

        $pages .= '</div>';
        $this->pages = $pages;
    }

    private function sofPages()
    {
        global $oDb;

        if(!$this->page) {
            $this->page = 1;
        }

        if(!$this->recCount) {
            $aSqlResult = $oDb->select("COUNT(*) as cnt", $this->sofSql["from"], $this->sofSql["where"]);
            $iCount = (isset($aSqlResult[0]))?$aSqlResult[0]["cnt"]:0;
        }
        else {
            $iCount = (int)$this->recCount;
        }

		$sPages = '';
        $iPerPage = (int)$this->perp;
        $iPage = (int)$this->page;
        $iPages = (int)(($iCount - 1)/$iPerPage + 1);
        if($iPages > 1) {
            $sPages .= '<div id="pages_block"><ul>';
            if($iPage == 1) {
                $sPages .= '<li class="page_back">'.$this->back.'</li>';
            }
            else {
                $sPages .= '<li class="page_back">'.str_replace(array('%page%','%text%'),array(($iPage - 1), $this->back),$this->link).'</li>';
            }

            if($this->pagesLim > 2 && $iPages > $this->pagesLim) {
                $iRealLim = $this->pagesLim - 2;
                $iB = $iPage;
                $iN = $iPage;
                while($iB > 2 && $iN < $iPages-1 && $iN-$iB+1 < $iRealLim) {
                    $iB--;
                    $iN++;
                }

                if($iB <= 2) {
                    $iB = 1;
                    $iN = $iRealLim + $iB;
                }
                else if($iN >= $iPages-1) {
                    $iN = $iPages;
                    $iB = $iN - $iRealLim;
                }

            }

            $i = 1;
            while($i <= $iPages) {
                if(isset($iB)) {
                    if($iB == 1 && $i > $iN && $i < $iPages) {
                        $sPages .= '<li class="page_dots">...</li>';
                        $i = $iPages;
                        continue;
                    }
                    else if($iN == $iPages && $i > 1 && $i < $iB) {
                        $sPages .= '<li class="page_dots">...</li>';
                        $i = $iB;
                        continue;
                    }
                    else if($iB > 1 && $iN < $iPages && $i > 1 && $i < $iB) {
                        $sPages .= '<li class="page_dots">...</li>';
                        $i = $iB;
                        continue;
                    }
                    else if($iB > 1 && $iN < $iPages && $i > 1 && $i > $iN && $i < $iPages) {
                        $sPages .= '<li class="page_dots">...</li>';
                        $i = $iPages;
                        continue;
                    }
                }

                if($i == $iPage) {
                    $sPages .= '<li class="page_current">'.$i.'</li>';
                }
                else {
                    $sPages .= '<li>'.str_replace(array('%page%','%text%'),$i,$this->link).'</li>';
                }

                $i++;
            }

            if($iPage == $iPages) {
                $sPages .= '<li class="page_next">'.$this->next.'</li>';
            }
            else {
                $sPages .= '<li class="page_back">'.str_replace(array('%page%','%text%'),array(($iPage + 1), $this->next),$this->link).'</li>';
            }

            $sPages .= '</ul></div>';
        }

        $this->pages = $sPages;
    }

    public function getPages($bWithString = false)
    {
        $this->pages();
        if(!$bWithString) {
            echo $this->pages;
        }

        return $this->pages;
    }

    public function getSofPages($bWithString = false)
    {
        $this->sofPages();
        if(!$bWithString) {
            echo $this->pages;
        }

        return $this->pages;
    }
}

function mq($q){$res=mysql_query($q) or die(mysql_error());return $res;}
function mfa($sql){return mysql_fetch_array($sql);}

 function countLet($s,$c){
        $cnt=0;
        for($i=0; $i<strlen($s); $i++){
             if($s[$i]==$c) $cnt++;
        }  return $cnt;
   }

   function lastPosition($src,$ch)
{
    for($i=strlen($src)-1; $i>=0; $i--)
    {
       if($src[$i]==$ch)
          return $i;
    }
    return false;
}

function getParent2($cat)
{
     return (lastPosition($cat,'/')!==false)?substr($cat,0,lastPosition($cat,'/')):'root';
}



function name($cat)
{
     return (strpos($cat,'/')!==false)?substr($cat,lastPosition($cat,'/')+1):$cat;
}

function toStr($ar)
{   $s='';
    for( $i=0; $i<count($ar); $i++)
       $s.=$ar[$i].'.';
    return $s.' &nbsp;';
}


function indexOf($el,$ar)
{
    for($i=0; $i<count($ar); $i++)
    {
        if($ar[$i]==$el)
            return $i;
    }
    return -1;
}

function isInArray($ar,$el)
{
   for($i=0;$i<count($ar);$i++)
      if($ar[$i]==$el) return true;
   return false;
}

function getArV($aArray, $sKey)
{
   return isset($aArray[$sKey])?$aArray[$sKey]:"";
}

?>