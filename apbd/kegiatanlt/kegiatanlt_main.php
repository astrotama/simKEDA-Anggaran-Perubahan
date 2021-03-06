<?php
function kegiatanlt_main($arg=NULL, $nama=NULL) {
	drupal_add_css('files/css/kegiatancam.css');
	//drupal_add_js('files/js/kegiatancam.js');
	switch($arg) {
		case 'show':
			$kodek='52';
			//$qlike = " and lower(kegiatan) like lower('%%%s%%')";
			//$qlike = sprintf(" and left(kodero,2)=\'%s\' and lower(kegiatan) like lower('%%%s%%')", $kodek);	
			$qlike = sprintf(' and left(kodero,2)=\'%s\' ', $kodek);	
			
			$_SESSION['kodeu'] = '';
			$_SESSION['kodepro'] = '';
			break;
		case 'filter':
			$kodeu = arg(3);
			$kodepro = arg(4);
			$_SESSION['kodeu'] = '';
			$_SESSION['kodepro'] = '';
			if (strlen($kodeu)>0) {
				$_SESSION['kodeu'] = $kodeu;
				$qlike .= sprintf(" and kodeu='%s' ", db_escape_string($kodeu));
				if (strlen($kodepro)>0) {
					$qlike .= sprintf(" and kodepro='%s' ", db_escape_string($kodepro));
					$_SESSION['kodepro'] = $kodepro;
				}
			}
			break;
		default:
			$_SESSION['kodeu'] = '';
			$_SESSION['kodepro'] = '';
			break;
	}
    $header = array (
        array('data' => 'No', 'width' => '10px', 'valign'=>'top'),
		array('data' => ucwords(strtolower('kode')), 'field'=> 'kodeu', 'valign'=>'top'),
		array('data' => ucwords(strtolower('kegiatan')), 'field'=> 'kegiatan', 'valign'=>'top'),

		array('data' => '', 'width' => '110px', 'valign'=>'top'),
    );
    $tablesort = tablesort_sql($header);
    if ($tablesort=='') {
        $tablesort=' order by kegid';
    }

    //$customwhere = ' and appkey=\'%s\'';
	$customwhere = ' ';
    $where = ' where true' . $customwhere . $qlike ;

    $sql = 'select kegid,u1,u2,np,nk,kegiatan,kodepro,kodeu from {kegiatanlt}' . $where;
    $fsql = sprintf($sql, addslashes($nama));
    $limit = 15;
    //echo $fsql;
    $countsql = "select count(*) as cnt from {kegiatanlt}" . $where;
    $fcountsql = sprintf($countsql, addslashes($nama));
    $result = pager_query($fsql . $tablesort, $limit, 0, $fcountsql);
    
    $no=0;
    $page = $_GET['page'];
    if (isset($page)) {
        $no = $page * $limit;
    } else {
        $no = 0;
    }
    if ($result) {
        while ($data = db_fetch_object($result)) {
			$editlink = "";
			//if (user_access('kegiatanlt edit'))
			//	$editlink .= l("<img src='/files/button-edit.png' title='Edit data'>", 'apbd/kegiatanlt/edit/' . $data->kegid, array('html'=>TRUE)) .'&nbsp;';
			//if (user_access('kegiatanlt penghapusan'))
         //       $editlink .=l("<img src='/files/button-delete.png' title='Hapus data'>", 'apbd/kegiatanlt/delete/' . $data->kegid, array('html'=>TRUE));
                
				if (user_access('kegiatanlt edit'))
					$namakeg = l($data->kegiatan, 'apbd/kegiatanlt/edit/' . $data->kegid, array('html'=>TRUE)). "&nbsp;";
				else 
					$namakeg = $data->kegiatan;
					
				if (user_access('kegiatanlt penghapusan'))
                $editlink =l('Hapus ', 'apbd/kegiatanlt/delete/' . $data->kegid, array('html'=>TRUE));
            
            $no++;
            $rows[] = array (
                array('data' => $no, 'align' => 'right'),
                
				array('data' => $data->kodeu . $data->np  . $data->nk , 'align' => 'left', 'valign'=>'top'),
				array('data' => $namakeg, 'align' => 'left', 'valign'=>'top'),
                array('data' => $editlink, 'align' => 'right'),
            );
        }
    } else { 
        $rows[] = array (
            array('data' => 'data kosong, silahkan menambahkan', 'colspan'=>'3')
        );
    }
	$btn = "";
	if (user_access('kegiatanlt tambah')) {
		$btn .= l('Baru', 'apbd/kegiatanlt/edit/', array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;'))) . "&nbsp;";
	}
	if (user_access('kegiatanlt pencarian'))	{
		$btn .= l('Cari', 'apbd/kegiatanlt/find/', array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;')));
	}
    $output .=  "<div id='fl_filter'>" . drupal_get_form ('kegiatanlt_filter_form') . "</div>" . $btn . theme_box('', theme_table($header, $rows)) . $btn;

//	if (user_access('kegiatanlt tambah'))
//		$output .= l("<img src='/files/button-add.png' title='Tambah data baru'>", 'apbd/kegiatanlt/edit/' , array('html'=>TRUE)) ;
//	if (user_access('kegiatanlt pencarian'))		
//        $output .= l("<img src='/files/button-search.png' title='Pencarian data'>", 'apbd/kegiatanlt/find/' , array('html'=>TRUE)) ;
    $output .= theme ('pager', NULL, $limit, 0);
    return $output;
}
function kegiatanlt_filter_form() {
	drupal_set_html_head('<style>label{font-weight: bold; display: block; width: 150px; float: left;}</style>');
    $form['formdata'] = array (
        '#type' => 'fieldset',
        '#title'=> 'Pilihan Data',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,        
    );
	
	$u = arg(3);
	$p = arg(4);
	$pquery = "select kodeu, urusansingkat from urusan order by kodeu";
	$pres = db_query($pquery);
	$urusan = array();
	$urusan[''] = '---SEMUA URUSAN---';
	
	while ($prow = db_fetch_object($pres)) {
		$urusan[$prow->kodeu] = $prow->kodeu . ' - '. $prow->urusansingkat ;
	}
	//$q = sprintf("select kodepro, program from program order by program");
	//$r = db_query($q);
	$program = array();
	$program[''] = '--PILIH PROGRAM--';
	//while ($d = db_fetch_object($r)) {
	//	$program[$d->kodepro] = $d->program;
	//}
	
    $form['formdata']['flurusan'] = array (
        '#type' => 'select',
        '#title' => 'Urusan',
		'#options' => $urusan,
		'#default_value' => $u,
    );
	$form['formdata']['fvprogram'] = array (
		'#type' => 'item',
		"#value"=> '<label for="edit-flurusan">Program: </label><select id="sprogram"><option value="">--SEMUA PROGRAM--</option></select>'
	);
    $form['formdata']['flprogram'] = array (
        '#type' => 'hidden',
        '#title' => 'Program',
		'#attributes' => array('style'=>'width: 500px'),
		//'#options' => $program,
		'#default_value' => $p,
    );
    $form['formdata']['submit'] = array (
        '#type' => 'submit',
        '#value' => 'Tampilkan',
    );
	
	return $form;
}
function kegiatanlt_filter_form_submit($form, &$form_state) {
	$urusan = $form_state['values']['flurusan'];
	$program = $form_state['values']['flprogram'];
	
	drupal_goto("apbd/kegiatanlt/filter/" . $urusan . "/" . $program);
}

function kegiatanlt_browse_asli($arg='normal', $nama=NULL) {
	switch($arg){
		case 'show':
			//$qlike = " and lower(kegiatan) like lower('%%%s%%')";
			$kodek = '52';
			$qlike = sprintf(' and left(kodero,2)=\'%s\' ', $kodek);
			break;
		case 'kodeu':
			$qlike = " and k.kodeu='%s'";
			break;
		case 'kodepro':
			$qlike = " and k.kodepro='%s'";
			break;
	}
	
    $header = array (
        array('data' => 'No', 'width' => '10px', 'valign'=>'top'),
		array('data' => ucwords(strtolower('kodepro')), 'field'=> 'kodepro', 'valign'=>'top', 'width' => '80px'),
		array('data' => ucwords(strtolower('nk')), 'field'=> 'nk', 'valign'=>'top'),
		array('data' => ucwords(strtolower('kegiatan')), 'field'=> 'k.kegiatan', 'valign'=>'top'),
		array('data' => '', 'width' => '110px', 'valign'=>'top'),
    );
    $tablesort = tablesort_sql($header);
    if ($tablesort=='') {
        $tablesort=' order by k.kegid';
    }

	$customwhere = ' ';
    $where = ' where true' . $customwhere . $qlike ;

    $sql = 'select k.kegid, k.u1, k.u2, k.np, k.nk, k.kegiatan, k.kodepro, k.kodeu, p.program, p.sifat, p.s2016 sasaran, p.t2016 target, p.tahun, u.kodebid from {kegiatanlt} k left join {program} p on (k.kodepro=p.kodepro) left join {urusan} u on (k.kodeu = u.kodeu) ' . $where;
    $fsql = sprintf($sql, addslashes($nama));
    $limit = 13;
    //echo $fsql;
    $countsql = "select count(*) as cnt from {kegiatanlt} k" . $where;
    $fcountsql = sprintf($countsql, addslashes($nama));
    $result = pager_query($fsql . $tablesort, $limit, 0, $fcountsql);
    
    $no=0;
    $page = $_GET['page'];
    if (isset($page)) {
        $no = $page * $limit;
    } else {
        $no = 0;
    }
    if ($result) {
        while ($data = db_fetch_object($result)) {
			$kodebid = $data->kodebid;
			if ($kodebid=='')
				$kodebid="00";
			$editlink = '';
			$attrlink = "kegiatan='" . $data->kegiatan .
						"' program='" . $data->program .
						"' kodepro='" . $data->kodepro .
						"' tahun='" . $data->tahun .
						"' nk='" . $data->nk .
						"' kodebid='" . $kodebid .
						"' sifat='" . $data->sifat .
						"' sasaran='" . $data->sasaran .
						"' target='" . $data->target . "'";
			$editlink = "<a href='#' class='btn_blue' " . $attrlink . " style='color:white;'>Pi l ih</a>";
            $no++;
            $rows[] = array (
                array('data' => $no, 'align' => 'right'),                
				array('data' => $data->kodepro, 'align' => 'left', 'valign'=>'top'),
				array('data' => $data->nk, 'align' => 'left', 'valign'=>'top'),
				array('data' => $data->kegiatan, 'align' => 'left', 'valign'=>'top'),
                array('data' => $editlink, 'align' => 'right'),
            );
        } 
    } else {
        $rows[] = array (
            array('data' => 'data kosong, silahkan menambahkan', 'colspan'=>'3')
        );
    } 
    $output .= theme_box('', theme_table($header, $rows));
    $output .= theme ('pager', NULL, $limit, 0);
	
	$pquery = "select kodeu, urusansingkat from urusan order by kodeu";
	$pres = db_query($pquery);
	$option = "<option value=''>---Pilih Urusan---</option>";
	while ($prow = db_fetch_object($pres)) {
		$option .= "<option value='" . $prow->kodeu . "'>" .  $prow->kodeu . ' - ' . $prow->urusansingkat . "</option>";
	}
	
	$rr[] = array (
		array('data' => 'Urusan', 'width' => '150px'),
		array('data' => "<select id='ur' style='width: 200px;'>" . $option. "</select>", 'width' => '200px'),
		array('data' => "<a href='#batal' class='btn_blue' style='color: #ffffff;'>Tutup</a>", 'align' => 'right'),
		//array('data' => "<input type='text' id='i_kg' value='' style='width: 150px;'/>", 'width'=>'150px'),
		//array('data' => "<a href='#sikg' class='btn_blue'>Cari</a>")
	);
	$rr[] = array (
		array('data' => 'Program', 'width' => '150px'),
		array('data' => "<select id='pr' style='width: 200px;'></select>", 'colspan'=>2),
		//array('data' => '&nbsp;', 'colspan'=>3)
	);
	
	 
	//echo $arg;
	if (!($arg == 'kodepro')) {
		$output ="<div id='pvtab'>" . $output . "</tab>";
		echo theme_box('', theme_table(array(), $rr));
	}
	
	echo $output;
}

function kegiatanlt_browsepad($arg='normal', $nama=NULL) {
	switch($arg){
		case 'show':
			$qlike = " and lower(uraian) like lower('%%%s%%')";
			break;
		case 'kodeu':
			$qlike = " and p.kodej='%s'";
			break;
		case 'kodepro':
			$qlike = " and k.kodeo='%s'";
			break;
	}
	
    $header = array (
		array('data' => 'Kode', 'field'=> 'kodero', 'valign'=>'top', 'width' => '80px'),
		array('data' => 'Uraian', 'field'=> 'kegiatan', 'valign'=>'top'),
		array('data' => 'Keterangan', 'field'=> 'ketrekening', 'valign'=>'top'),
		array('data' => '', 'width' => '110px', 'valign'=>'top'),
    );
    $tablesort = tablesort_sql($header);
    if ($tablesort=='') {
        $tablesort=' order by k.kodero';
    }

	if (isSuperuser()) {
		$kodek = '4';
		$customwhere = sprintf(' and left(k.kodero,1)=\'%s\' ', $kodek);
	} else {
		
		if (apbd_getuseruk()=='81') {
			$kodek = '4';
			$customwhere = sprintf(' and left(k.kodero,1)=\'%s\' ', $kodek);
		}
		else {
			$kodek = '41';
			$customwhere = sprintf(' and left(k.kodero,2)=\'%s\' ', $kodek);
		}
	}
	
	//$customwhere = ' ';
    $where = ' where true' . $customwhere . $qlike ;

    $sql = 'select k.kodero, k.uraian, ketrekening from {rincianobyek} k ' . $where;
    $fsql = sprintf($sql, addslashes($nama));
    $limit = 12;
    //echo $fsql;
    $countsql = "select count(*) as cnt from {rincianobyek} k" . $where;
    $fcountsql = sprintf($countsql, addslashes($nama));
    $result = pager_query($fsql . $tablesort, $limit, 0, $fcountsql);
    
    $no=0;
    $page = $_GET['page'];
    if (isset($page)) {
        $no = $page * $limit;
    } else {
        $no = 0;
    }
    if ($result) {
        while ($data = db_fetch_object($result)) {
			$editlink = '';
			$attrlink = "kegiatan='" . $data->uraian .
						"' nk='" . $data->kodero . 
						"' ketrekening='" . $data->ketrekening . "'";
						
			$editlink = "<a href='#' class='btn_blue' " . $attrlink . " style='color:white;'>Pi l ih</a>";
            $no++;
            $rows[] = array (
				array('data' => $data->kodero, 'align' => 'left', 'valign'=>'top'),
				array('data' => $data->uraian, 'align' => 'left', 'valign'=>'top'),
				array('data' => $data->ketrekening, 'align' => 'left', 'valign'=>'top'),
                array('data' => $editlink, 'align' => 'right'),
            );
        } 
    } else {
        $rows[] = array (
            array('data' => 'data kosong, silahkan menambahkan', 'colspan'=>'3')
        );
    } 
    $output .= theme_box('', theme_table($header, $rows));
    $output .= theme ('pager', NULL, $limit, 0);
	
	if (isSuperuser()) 
		$pquery = 'select kodej kodeu, uraian urusansingkat from jenis where left(kodek,1)=\'%s\' order by kodej';
	else {
	
		if (apbd_getuseruk()=='81') {
			$pquery = 'select kodej kodeu, uraian urusansingkat from jenis where left(kodek,1)=\'%s\' order by kodej';

			//$pquery = 'select kodej kodeu, uraian urusansingkat from jenis where kodek=\'%s\' order by kodej';
			
			
		} else
			$pquery = 'select kodej kodeu, uraian urusansingkat from jenis where kodek=\'%s\' order by kodej';
	}
	
	$pres = db_query(db_rewrite_sql($pquery), array($kodek));
	$option = "<option value=''>- Pilih Jenis Rekening -</option>";
	while ($prow = db_fetch_object($pres)) {
		$option .= "<option value='" . $prow->kodeu . "'>" .  $prow->kodeu . ' - ' . $prow->urusansingkat . "</option>";
	}
	
	$rr[] = array (
		array('data' => 'Jenis', 'width' => '150px'),
		//array('data' => "<select id='ur' style='width: 500px;'>" . $option. "</select>", 'width' => '200px'),
		array('data' => "<select id='ur' style='width: 500px;'>" . $option. "</select>"),
		//array('data' => 'Rekening', 'width' => '150px', 'align'=>'right'),
		//array('data' => "<input type='text' id='i_kg' value='' style='width: 150px;'/>", 'width'=>'150px'),
		array('data' => "<a href='#batal' class='btn_blue' style='color: #ffffff;'>Tutup</a>", 'align' => 'right'),
		//array('data' => "<a href='#sikg' class='btn_blue'>Cari</a>")
	);
	$rr[] = array ( 
		array('data' => 'Obyek', 'width' => '150px'),
		array('data' => "<select id='pr' style='width: 500px;'></select>", 'colspan'=>2),
		//array('data' => "<select id='pr' style='width: 500px;'></select>"),
		//array('data' => 'Rekening', 'width' => '150px', 'align'=>'right'),
		//array('data' => "<input type='text' id='i_kg' value='' style='width: 150px;'/>", 'width'=>'150px'),
		//array('data' => '&nbsp;', 'colspan'=>3)
	);
	
	
	//echo $arg;
	if (!($arg == 'kodepro')) {
	//if (!(($arg == 'kodepro')||($arg=='show'))) {
		$output ="<div id='pvtab'>" . $output . "</tab>";
		echo theme_box('', theme_table(array(), $rr));
	}
	
	echo $output;
}

function kegiatanlt_browse($arg='normal', $nama=NULL) {
	switch($arg){
		case 'show':
			$qlike = " and lower(uraian) like lower('%%%s%%')";
			break;
		case 'kodeu':
			$qlike = " and p.kodej='%s'";
			break;
		case 'kodepro':
			$qlike = " and k.kodeo='%s'";
			break;
	}
	
    $header = array (
		array('data' => 'Kode', 'field'=> 'nk', 'valign'=>'top', 'width' => '80px'),
		array('data' => 'Uraian', 'field'=> 'kegiatan', 'valign'=>'top'),
		array('data' => '', 'width' => '110px', 'valign'=>'top'),
    );
    $tablesort = tablesort_sql($header);
    if ($tablesort=='') {
        $tablesort=' order by k.kodero';
    }

	$kodek = '52';
	$customwhere = sprintf(' and left(k.kodero,2)=\'%s\' ', $kodek);
	
	//$customwhere = ' ';
    $where = ' where true' . $customwhere . $qlike ;

    $sql = 'select k.kodero nk, k.uraian kegiatan from {rincianobyek} k ' . $where;
    $fsql = sprintf($sql, addslashes($nama));
    $limit = 12;
    //echo $fsql;
    $countsql = "select count(*) as cnt from {rincianobyek} k" . $where;
    $fcountsql = sprintf($countsql, addslashes($nama));
    $result = pager_query($fsql . $tablesort, $limit, 0, $fcountsql);
    
    $no=0;
    $page = $_GET['page'];
    if (isset($page)) {
        $no = $page * $limit;
    } else {
        $no = 0;
    }
    if ($result) {
        while ($data = db_fetch_object($result)) {
			$editlink = '';
			$attrlink = "kegiatan='" . $data->kegiatan .
						"' nk='" . $data->nk . "'";
			$editlink = "<a href='#' class='btn_blue' " . $attrlink . " style='color:white;'>Pi l ih</a>";
            $no++;
            $rows[] = array (
				array('data' => $data->nk, 'align' => 'left', 'valign'=>'top'),
				array('data' => $data->kegiatan, 'align' => 'left', 'valign'=>'top'),
                array('data' => $editlink, 'align' => 'right'),
            );
        } 
    } else {
        $rows[] = array (
            array('data' => 'data kosong, silahkan menambahkan', 'colspan'=>'3')
        );
    } 
    $output .= theme_box('', theme_table($header, $rows));
    $output .= theme ('pager', NULL, $limit, 0);
	
$pquery = 'select kodej kodeu, uraian urusansingkat from jenis where kodek=\'%s\' order by kodej';
	
	$pres = db_query(db_rewrite_sql($pquery), array($kodek));
	$option = "<option value=''>- Pilih Jenis Rekening -</option>";
	$option .= "<option value='000'>000 - REKENING TAHUN SEBELUMNYA</option>";
	while ($prow = db_fetch_object($pres)) {
		$option .= "<option value='" . $prow->kodeu . "'>" .  $prow->kodeu . ' - ' . $prow->urusansingkat . "</option>";
	}
	
	$rr[] = array (
		array('data' => 'Jenis', 'width' => '150px'),
		//array('data' => "<select id='ur' style='width: 500px;'>" . $option. "</select>", 'width' => '200px'),
		array('data' => "<select id='ur' style='width: 500px;'>" . $option. "</select>"),
		//array('data' => 'Rekening', 'width' => '150px', 'align'=>'right'),
		//array('data' => "<input type='text' id='i_kg' value='' style='width: 150px;'/>", 'width'=>'150px'),
		array('data' => "<a href='#batal' class='btn_blue' style='color: #ffffff;'>Tutup</a>", 'align' => 'right'),
		//array('data' => "<a href='#sikg' class='btn_blue'>Cari</a>")
	);
	$rr[] = array ( 
		array('data' => 'Obyek', 'width' => '150px'),
		array('data' => "<select id='pr' style='width: 500px;'></select>", 'colspan'=>2),
		//array('data' => "<select id='pr' style='width: 500px;'></select>"),
		//array('data' => 'Rekening', 'width' => '150px', 'align'=>'right'),
		//array('data' => "<input type='text' id='i_kg' value='' style='width: 150px;'/>", 'width'=>'150px'),
		//array('data' => '&nbsp;', 'colspan'=>3)
	);
	
	
	//echo $arg;
	if (!($arg == 'kodepro')) {
	//if (!(($arg == 'kodepro')||($arg=='show'))) {
		$output ="<div id='pvtab'>" . $output . "</tab>";
		echo theme_box('', theme_table(array(), $rr));
	}
	
	echo $output;
}

function kegiatanlt_browsebtl($arg='normal', $nama=NULL) {
	switch($arg){
		case 'show':
			$qlike = " and lower(uraian) like lower('%%%s%%')";
			break;
		case 'kodeu':
			$qlike = " and p.kodej='%s'";
			break;
		case 'kodepro':
			$qlike = " and k.kodeo='%s'";
			break;
	}
	
    $header = array (
		array('data' => 'Kode', 'field'=> 'nk', 'valign'=>'top', 'width' => '80px'),
		array('data' => 'Uraian', 'field'=> 'kegiatan', 'valign'=>'top'),
		array('data' => '', 'width' => '110px', 'valign'=>'top'),
    );
    $tablesort = tablesort_sql($header);
    if ($tablesort=='') {
        $tablesort=' order by k.kodero';
    }
	
	$kodek = '51';
	if (isSuperuser())
		$customwhere = sprintf(' and left(k.kodero,2)=\'%s\' ', $kodek);
		//$customwhere = sprintf(' and left(k.kodero,3)=\'%s\' ', $kodej);
	else
		$customwhere = " and left(k.kodero,3)='511' ";
	
	
	//$customwhere = ' ';
    $where = ' where true' . $customwhere . $qlike ;

    $sql = 'select k.kodero nk, k.uraian kegiatan from {rincianobyek} k ' . $where;
    $fsql = sprintf($sql, addslashes($nama));
    $limit = 12;
    //echo $fsql;
    $countsql = "select count(*) as cnt from {rincianobyek} k" . $where;
    $fcountsql = sprintf($countsql, addslashes($nama));
    $result = pager_query($fsql . $tablesort, $limit, 0, $fcountsql);
    
    $no=0;
    $page = $_GET['page'];
    if (isset($page)) {
        $no = $page * $limit;
    } else {
        $no = 0;
    }
    if ($result) {
        while ($data = db_fetch_object($result)) {
			$editlink = '';
			$attrlink = "kegiatan='" . $data->kegiatan .
						"' nk='" . $data->nk . "'";
			$editlink = "<a href='#' class='btn_blue' " . $attrlink . " style='color:white;'>Pi l ih</a>";
            $no++;
            $rows[] = array (
				array('data' => $data->nk, 'align' => 'left', 'valign'=>'top'),
				array('data' => $data->kegiatan, 'align' => 'left', 'valign'=>'top'),
                array('data' => $editlink, 'align' => 'right'),
            );
        } 
    } else {
        $rows[] = array (
            array('data' => 'data kosong, silahkan menambahkan', 'colspan'=>'3')
        );
    } 
    $output .= theme_box('', theme_table($header, $rows));
    $output .= theme ('pager', NULL, $limit, 0);
	
	if (isSuperuser()) {
		$pquery = 'select kodej kodeu, uraian urusansingkat from jenis where kodek=\'%s\' order by kodej';
		$pres = db_query(db_rewrite_sql($pquery), array($kodek));
	} else {
		$pquery = "select kodej kodeu, uraian urusansingkat from jenis where kodej='511' order by kodej";
		$pres = db_query($pquery);
	}
		
	$option = "<option value=''>- Pilih Jenis Rekening -</option>";
	while ($prow = db_fetch_object($pres)) {
		$option .= "<option value='" . $prow->kodeu . "'>" .  $prow->kodeu . ' - ' . $prow->urusansingkat . "</option>";
	}
	
	$rr[] = array (
		array('data' => 'Jenis', 'width' => '150px'),
		//array('data' => "<select id='ur' style='width: 500px;'>" . $option. "</select>", 'width' => '200px'),
		array('data' => "<select id='ur' style='width: 500px;'>" . $option. "</select>"),
		//array('data' => 'Rekening', 'width' => '150px', 'align'=>'right'),
		//array('data' => "<input type='text' id='i_kg' value='' style='width: 150px;'/>", 'width'=>'150px'),
		array('data' => "<a href='#batal' class='btn_blue' style='color: #ffffff;'>Tutup</a>", 'align' => 'right'),
		//array('data' => "<a href='#sikg' class='btn_blue'>Cari</a>")
	);
	$rr[] = array ( 
		array('data' => 'Obyek', 'width' => '150px'),
		array('data' => "<select id='pr' style='width: 500px;'></select>", 'colspan'=>2),
		//array('data' => "<select id='pr' style='width: 500px;'></select>"),
		//array('data' => 'Rekening', 'width' => '150px', 'align'=>'right'),
		//array('data' => "<input type='text' id='i_kg' value='' style='width: 150px;'/>", 'width'=>'150px'),
		//array('data' => '&nbsp;', 'colspan'=>3)
	);
	
	
	//echo $arg;
	if (!($arg == 'kodepro')) {
	//if (!(($arg == 'kodepro')||($arg=='show'))) {
		$output ="<div id='pvtab'>" . $output . "</tab>";
		echo theme_box('', theme_table(array(), $rr));
	}
	
	echo $output;
}

function kegiatanlt_browsepb($arg='normal', $nama=NULL) {
	switch($arg){
		case 'show':
			$qlike = " and lower(uraian) like lower('%%%s%%')";
			break;
		case 'kodeu':
			$qlike = " and p.kodej='%s'";
			break;
		case 'kodepro':
			$qlike = " and k.kodeo='%s'";
			break;
	}
	
    $header = array (
		array('data' => 'Kode', 'field'=> 'nk', 'valign'=>'top', 'width' => '80px'),
		array('data' => 'Uraian', 'field'=> 'kegiatan', 'valign'=>'top'),
		array('data' => '', 'width' => '110px', 'valign'=>'top'),
    );
    $tablesort = tablesort_sql($header);
    if ($tablesort=='') {
        $tablesort=' order by k.kodero';
    }

	$kodek = '6';
	$customwhere = sprintf(' and left(k.kodero,1)=\'%s\' ', $kodek);
	
	//$customwhere = ' ';
    $where = ' where true' . $customwhere . $qlike ;

    $sql = 'select k.kodero nk, k.uraian kegiatan from {rincianobyek} k ' . $where;
    $fsql = sprintf($sql, addslashes($nama));
    $limit = 12;
    //echo $fsql;
    $countsql = "select count(*) as cnt from {rincianobyek} k" . $where;
    $fcountsql = sprintf($countsql, addslashes($nama));
    $result = pager_query($fsql . $tablesort, $limit, 0, $fcountsql);
    
    $no=0;
    $page = $_GET['page'];
    if (isset($page)) {
        $no = $page * $limit;
    } else {
        $no = 0;
    }
    if ($result) {
        while ($data = db_fetch_object($result)) {
			$editlink = '';
			$attrlink = "kegiatan='" . $data->kegiatan .
						"' nk='" . $data->nk . "'";
			$editlink = "<a href='#' class='btn_blue' " . $attrlink . " style='color:white;'>Pi l ih</a>";
            $no++;
            $rows[] = array (
				array('data' => $data->nk, 'align' => 'left', 'valign'=>'top'),
				array('data' => $data->kegiatan, 'align' => 'left', 'valign'=>'top'),
                array('data' => $editlink, 'align' => 'right'),
            );
        } 
    } else {
        $rows[] = array (
            array('data' => 'data kosong, silahkan menambahkan', 'colspan'=>'3')
        );
    } 
    $output .= theme_box('', theme_table($header, $rows));
    $output .= theme ('pager', NULL, $limit, 0);
	
	$pquery = 'select kodej kodeu, uraian urusansingkat from jenis where left(kodek,1)=\'%s\' order by kodej';
	
	$pres = db_query(db_rewrite_sql($pquery), array($kodek));
	$option = "<option value=''>- Pilih Jenis Rekening -</option>";
	while ($prow = db_fetch_object($pres)) {
		$option .= "<option value='" . $prow->kodeu . "'>" .  $prow->kodeu . ' - ' . $prow->urusansingkat . "</option>";
	}
	
	$rr[] = array (
		array('data' => 'Jenis', 'width' => '150px'),
		//array('data' => "<select id='ur' style='width: 500px;'>" . $option. "</select>", 'width' => '200px'),
		array('data' => "<select id='ur' style='width: 500px;'>" . $option. "</select>"),
		//array('data' => 'Rekening', 'width' => '150px', 'align'=>'right'),
		//array('data' => "<input type='text' id='i_kg' value='' style='width: 150px;'/>", 'width'=>'150px'),
		array('data' => "<a href='#batal' class='btn_blue' style='color: #ffffff;'>Tutup</a>", 'align' => 'right'),
		//array('data' => "<a href='#sikg' class='btn_blue'>Cari</a>")
	);
	$rr[] = array ( 
		array('data' => 'Obyek', 'width' => '150px'),
		array('data' => "<select id='pr' style='width: 500px;'></select>", 'colspan'=>2),
		//array('data' => "<select id='pr' style='width: 500px;'></select>"),
		//array('data' => 'Rekening', 'width' => '150px', 'align'=>'right'),
		//array('data' => "<input type='text' id='i_kg' value='' style='width: 150px;'/>", 'width'=>'150px'),
		//array('data' => '&nbsp;', 'colspan'=>3)
	);
	
	
	//echo $arg;
	if (!($arg == 'kodepro')) {
	//if (!(($arg == 'kodepro')||($arg=='show'))) {
		$output ="<div id='pvtab'>" . $output . "</tab>";
		echo theme_box('', theme_table(array(), $rr));
	}
	
	echo $output;
}

function kegiatanlt_browserev($arg='normal', $nama=NULL) {
	switch($arg){
		case 'show':
			$qlike = " and lower(kegiatan) like lower('%%%s%%')";
			break;

		case 'filter':
			//$qlike .= sprintf(" and kodeu='%s' ", db_escape_string($kodeu));
			$kodeuk = arg(4);
			$kegcari = arg(5);
			$qlike = sprintf(" and k.kodeuk='%s' ", db_escape_string($kodeuk));
			
			if (isset($kegcari))
				$qlike .= sprintf(" and lower(kegiatan) like lower('%%%s%%') ", db_escape_string($kegcari));	
				
			break;

	}
	
	if (isSuperuser()) 
		$header = array (
			array('data' => 'No', 'width' => '10px', 'valign'=>'top'),
			array('data' => 'SKPD', 'field'=> 'namasingkat', 'valign'=>'top'),
			array('data' => 'Kegiatan', 'field'=> 'kegiatan', 'valign'=>'top'),
			array('data' => 'Plafon', 'field'=> 'plafon', 'valign'=>'top'),
			array('data' => 'Anggaran', 'field'=> 'total', 'valign'=>'top'),
			//array('data' => '', 'width' => '110px', 'valign'=>'top'),
			array('data' => "<a href='#batal' class='btn_blue' style='color: #ffffff;'>Tutup</a>", 'align' => 'right'),

		);
	else
		$header = array (
			array('data' => 'No', 'width' => '10px', 'valign'=>'top'),
			array('data' => 'Kegiatan', 'field'=> 'k.kegiatan', 'valign'=>'top'),
			array('data' => 'Plafon', 'field'=> 'plafon', 'valign'=>'top'),
			array('data' => 'Anggaran', 'field'=> 'total', 'valign'=>'top'),
			//array('data' => '', 'width' => '110px', 'valign'=>'top'),
			array('data' => "<a href='#batal' class='btn_blue' style='color: #ffffff;'>Tutup</a>", 'align' => 'right'),

		);
		
	$tablesort = tablesort_sql($header);
    if ($tablesort=='') {
        $tablesort=' order by k.kegiatan';
    }

	//if (!isSuperuser()) 
	//	$customwhere = sprintf(' and k.kodeuk=\'%s\' ', apbd_getuseruk());
    $where = ' where true' . $customwhere . $qlike ;

    $sql = 'select k.kodekeg, k.kegiatan, k.kodeuk, k.kodepro, p.program, k.plafon, k.total, u.namasingkat 
			from {kegiatanskpd} k inner join {program} p on (k.kodepro=p.kodepro) inner join {unitkerja} u on k.kodeuk=u.kodeuk ' . $where;
    //$fsql = sprintf($sql, addslashes($nama));
	$fsql = $sql;
    $limit = 10;
    //drupal_set_message($fsql);
    $countsql = "select count(*) as cnt from {kegiatanskpd} k" . $where;
    $fcountsql = sprintf($countsql, addslashes($nama));
    $result = pager_query($fsql . $tablesort, $limit, 0, $fcountsql);
    
    $no=0;
    $page = $_GET['page'];
    if (isset($page)) {
        $no = $page * $limit;
    } else {
        $no = 0;
    }
    if ($result) {
        while ($data = db_fetch_object($result)) {
			$editlink = '';
			$attrlink = "kegiatan='" . $data->kegiatan .
						"' kodekeg='" . $data->kodekeg .
						"' program='" . $data->program .
						"' kodepro='" . $data->kodepro .
						"' kodeuk='" . $data->kodeuk .
						"' total='" . $data->total .
						"' plafon='" . $data->plafon . "'";
			$editlink = "<a href='#' class='btn_blue' " . $attrlink . " style='color:white;'>Pi l ih</a>";
            $no++;
			if (isSuperuser())
				$rows[] = array (
					array('data' => $no, 'align' => 'right'),                
					array('data' => $data->namasingkat, 'align' => 'left', 'valign'=>'top'),
					array('data' => $data->kegiatan, 'align' => 'left', 'valign'=>'top'),
					array('data' => apbd_fn($data->plafon), 'align' => 'right', 'valign'=>'top'),
					array('data' => apbd_fn($data->total), 'align' => 'right', 'valign'=>'top'),
					array('data' => $editlink, 'align' => 'right'),
				);
			else
				$rows[] = array (
					array('data' => $no, 'align' => 'right'),                
					array('data' => $data->kegiatan, 'align' => 'left', 'valign'=>'top'),
					array('data' => apbd_fn($data->plafon), 'align' => 'right', 'valign'=>'top'),
					array('data' => apbd_fn($data->total), 'align' => 'right', 'valign'=>'top'),
					array('data' => $editlink, 'align' => 'right'),
				);
				
        } 
    } else {
        $rows[] = array (
            //array('data' => 'data kosong, silahkan menambahkan', 'colspan'=>'3')
			array('data' => $fsql, 'colspan'=>'4')
        );
    } 
    $output .= theme_box('', theme_table($header, $rows));
    $output .= theme ('pager', NULL, $limit, 0);

	
	 
	//echo $arg;
	//if (!($arg == 'kodepro')) {
	if (!($arg == 'kodeu')) {
		$output ="<div id='pvtab'>" . $output . "</tab>";
		echo theme_box('', theme_table(array(), $rr));
	}
	
	echo $output;
}
?>