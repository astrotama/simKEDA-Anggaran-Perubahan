<?php
function kegiatanrevisisemua_main($arg=NULL, $nama=NULL) {
    $h = '<style>label{font-weight: bold; display: block; width: 150px; float: left;}</style>';
    drupal_set_html_head($h);
	drupal_add_css('files/css/kegiatancam.css');
	drupal_add_js('files/js/kegiatancam.js');
	$qlike='';
	$limit = 15;
	
	$tahun = variable_get('apbdtahun', 0);
    if ($arg) {
		switch($arg) {
			case 'show':
				//$qlike = " and lower(k.kegiatan) like lower('%%%s%%')";    
				$qlike = sprintf(" and lower(kegiatan) like lower('%%%s%%') ", db_escape_string(arg(3)));	
				//drupal_set_message(arg(4));
				break;
			case 'filter':
				$kodeuk = arg(3);
				$sumberdana = arg(4);
				$statusinaktif = arg(5);
				$jenis = arg(6);
				$statusperubahan = arg(7);
				$kegiatanparam = arg(8);
				$exportpdf = arg(9);
				
				break;

			case 'excel':
				$kodeuk = arg(3);
				kegiatanrevisisemua_exportexcel($kodeuk);
				break;
				
			default:
				drupal_access_denied();
				break;
		}
	} else {
		/*
		$tahun = variable_get('apbdtahun', 0);
		$sumberdana = $_SESSION['sumberdana'];
		$statusinaktif = $_SESSION['statusinaktif'];
		$jenis = $_SESSION['jenis'];
		
		$kodeuk = $_SESSION['kodeuk'];
		if ($kodeuk == '') 	$kodeuk = 'ZZ';
		*/

		$sumberdana = 'ZZ';
		$statusinaktif = 'ZZ';
		$jenis = 'ZZ';
		$statusinaktif = 'ZZ';
		$statusperubahan = 'ZZ';
		$kodeuk = 'ZZ';
		$kegiatanparam = '';
	}


	//SUMBER DANA
	if ($kodeuk != 'ZZ') {
		$qlike = sprintf(' and kodeuk=\'%s\' ', $kodeuk);
	}
	

	//STATUS INAKTIF
	if ($statusinaktif=='0') {
		$qlike .= sprintf(' and inaktif=0 ');
	} elseif ($statusinaktif=='1') {
		$qlike .= sprintf(' and (inaktif=1 or plafon=0) ');
	} elseif ($statusinaktif=='2') {
		$qlike .= sprintf(' and (inaktif=0) and (plafon<anggaran) ');
	} elseif ($statusinaktif=='3') {
		$qlike .= sprintf(' and (inaktif=0) and (plafon>anggaran) ');
	} 

	//STATUS PERUBAHAN
	if ($statusperubahan=='1')
		$tbl_name = 'q_kegiatanadarevisi';
	elseif ($statusperubahan=='0')
		$tbl_name = 'q_kegiatantidakrevisi';
	else
		$tbl_name = 'q_kegiatanrevisisemua';
	
	//STATUS INAKTIF
	/*
			 'gaji' => t('GAJI'), 	
			 'subsidi' => t('SUBSIDI'),
			 'hibah' => t('HIBAH'),
			 'bansos' => t('BANTUAN SOSIAL'),
			 'bagihasil' => t('BAGI HASIL'),
			 'bankeu' => t('BANTUAN KEUANGAN'),
			 'langsung' => t('LANGSUNG'),
			 'pegawai' => t('PEGAWAI'),
			 'barangjasa' => t('BARANG DAN JASA'),
			 'modal' => t('MODAL'),		
	*/
	$sqlrek = '';
	if ($jenis=='gaji') {
		$qlike .= sprintf(' and jenis=1 and isppkd=0 ');
	} elseif ($jenis=='langsung') {
		$qlike .= sprintf(' and jenis=2 ');
	} elseif ($jenis=='ppkd') {
		$qlike .= sprintf(' and jenis=1 and isppkd=1 ');
	} elseif ($jenis=='subsidi') {
		$sqlrek = " and kodekeg in (select kodekeg from q_anggperkegrevisiproses where kodero like '513%')";
	} elseif ($jenis=='hibah') {
		$sqlrek = " and kodekeg in (select kodekeg from q_anggperkegrevisiproses where kodero like '514%')";
	} elseif ($jenis=='bansos') {
		$sqlrek = " and kodekeg in (select kodekeg from q_anggperkegrevisiproses where kodero like '515%')";
	} elseif ($jenis=='bagihasil') {
		$sqlrek = " and kodekeg in (select kodekeg from q_anggperkegrevisiproses where kodero like '516%')";
	} elseif ($jenis=='bankeu') {
		$sqlrek = " and kodekeg in (select kodekeg from q_anggperkegrevisiproses where kodero like '517%')";
	} elseif ($jenis=='pegawai') {
		$sqlrek = " and kodekeg in (select kodekeg from q_anggperkegrevisiproses where kodero like '521%')";
	} elseif ($jenis=='barangjasa') {
		$sqlrek = " and kodekeg in (select kodekeg from q_anggperkegrevisiproses where kodero like '522%')";
	} elseif ($jenis=='modal') {
		$sqlrek = " and kodekeg in (select kodekeg from q_anggperkegrevisiproses where kodero like '523%')";
	}
	
	//SUMBER DANA
	if ($sumberdana != 'ZZ') {
		$qlike .= sprintf(' and sumberdana=\'%s\' ', $sumberdana);
	}
 
	//drupal_set_message($kegiatan);
	if ($kegiatanparam!='') {
		//$qlike .= sprintf(" and kegiatan like binary '%%%s%%' ", db_escape_string(strtolower($kegiatanparam)));	
		//$qlike .= sprintf(" and kegiatan like '%%%s%%' ", db_escape_string($kegiatanparam));	
		
		$qlike .= " and kegiatan like binary '%" . $kegiatanparam . "%' ";	
	}
	//drupal_set_message($qlike);
	
    $where = ' where true' .  $qlike . $sqlrek ;

	$fsql = "select kodekeg, kodeuk, kegiatan,jenis, lokasi, programtarget,penetapan, anggaran, plafon, inaktif, isppkd, namasingkat, sumberdana, revisi from " . $tbl_name . $where;
	//drupal_set_message($fsql);
	
	if (isset($exportpdf))   {
		$pdfFile = 'Daftar_Kegiatan_Perubahan.pdf';
		
		$htmlHeader = GenDataHeader($kodeuk);
		$htmlContent = GenDataPrint($kodeuk, $fsql);
		
		apbd_ExportPDF2('L', 'F4', $htmlHeader, $htmlContent, $pdfFile);
		return null;
		
	} else {
	
		//$output .= drupal_get_form('kegiatanrevisisemua_transfer_form');
		$output .= drupal_get_form('kegiatanrevisisemua_main_form');
		$header = array (
			array('data' => 'No', 'width' => '10px', 'valign'=>'top'),
			array('data' => '', 'width' => '5px', 'valign'=>'top'),
			array('data' => '', 'width' => '5px', 'valign'=>'top'),
			array('data' => 'SKPD', 'field'=> 'namasingkat', 'valign'=>'top'),
			array('data' => 'Kegiatan', 'field'=> 'kegiatan', 'valign'=>'top'),
			//array('data' => 'Lokasi',  'valign'=>'top'),
			array('data' => 'Sumberdana', 'field'=> 'sumberdana', 'valign'=>'top'),
			array('data' => 'Penetapan', 'field'=> 'penetapan','width' => '90px', 'valign'=>'top'),
			array('data' => 'Perubahan', 'field'=> 'anggaran','width' => '90px', 'valign'=>'top'),
			array('data' => 'Plafon', 'field'=> 'plafon','width' => '90px', 'valign'=>'top'),
			array('data' => '', 'width' => '40px', 'valign'=>'top'),
		);

		$tablesort = tablesort_sql($header);
		if ($tablesort=='') {
			$tablesort=' order by kegiatan';
		}
	
		//echo $fsql;
		$countsql = "select count(*) as cnt from " . $tbl_name . $where;
		//$fcountsql = sprintf($countsql, addslashes($nama));
		$fcountsql = $countsql;
		$result = pager_query($fsql . $tablesort, $limit, 0, $fcountsql);

		//Jam,Menit,Detik,Bulan,Hari,Tahun
		//$batas = mktime(20, 0, 0, 6, 16, 2015) ;
		//$sekarang = time () ;
		//$selisih =($batas-$sekarang) ;
		$allowedit = true;		//(($selisih>0) || (isSuperuser()));
		
		//CEK TAHUN
		//$allowedit = ($allowedit and ($tahun == variable_get('apbdtahun', 0)));
		
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
				
			
				if ($data->revisi=='0') {
					$editlink = '';
					$kegiatan = l($data->kegiatan, 'apbd/kegiatanrevisiperubahan/editadminbu/' . $data->kodekeg, array('html'=>TRUE));
					
					$editlink = l('Usulkan', 'apbd/kegiatanrevisi/editperubahan/0/' . $data->kodeuk . '/' . $data->kodekeg  . '/0', array('html'=>TRUE));
					
				} else {
					
					$kegiatan = l('<strong>' . $data->kegiatan . '</strong>', 'apbd/kegiatanrevisiperubahan/editadmin/' . $data->kodekeg, array('html'=>TRUE));
					
					$editlink = l('Rek', 'apbd/kegiatanrevisiperubahan/rekening/' . $data->kodekeg  . '/0', array('html'=>TRUE));
					$editlink .= "&nbsp;" . l('TW', 'apbd/kegiatanrevisiperubahan/triwulan/' . $data->kodekeg . '/0', array('html'=>TRUE));
				}
				
				$no++;
				
				if ($data->anggaran > $data->plafon)
					$limit = "<img src='/files/limit.png'>";
				else
					$limit = '';

				if ($data->inaktif) {
					//$inaktif = 'x';
					$inaktif = "<img src='/files/inaktif.png'>";
					$anggaran = 0;
				
				} else {
					$anggaran = $data->anggaran;
					$inaktif ='';
				}
				
				$rows[] = array (
					array('data' => $no, 'align' => 'right', 'valign'=>'top'),
					array('data' => $inaktif, 'align' => 'center', 'color' => 'red', 'valign'=>'top'),
					array('data' => $limit, 'align' => 'center', 'color' => 'red', 'valign'=>'top'),
					array('data' => $data->namasingkat, 'align' => 'left', 'valign'=>'top'),
					array('data' => $kegiatan, 'align' => 'left', 'valign'=>'top'),
					array('data' => $data->sumberdana, 'align' => 'left', 'valign'=>'top'),
					array('data' => apbd_fn($data->penetapan), 'align' => 'right', 'valign'=>'top'),
					array('data' => apbd_fn($anggaran), 'align' => 'right', 'valign'=>'top'),
					array('data' => apbd_fn($data->plafon), 'align' => 'right', 'valign'=>'top'),
					array('data' => $editlink, 'align' => 'left', 'valign'=>'top'),
				);

			}
		} else {
			$rows[] = array (
				array('data' => 'data kosong, silahkan menambahkan', 'colspan'=>'3')
			);
		}

		$btn = l('Cetak', 	'apbd/kegiatanperubahansemua/filter/' . $kodeuk . '/' . $sumberdana . '/' . $statusinaktif . '/' . $jenis . '/' . $statusperubahan . '/' . $kegiatanparam . '/pdf', array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;')));

		//$btn .= "&nbsp;" . l("Cari", 'apbd/kegiatanskpd/find/' , array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;'))) ;
		
		//$btn .= "&nbsp;" . l('Simpan Excel', 'apbd/kegiatanskpd/excel/' . $kodeuk , array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;')));	
		
		$output .= $btn . theme_box('', theme_table($header, $rows)) . $btn;


		$output .= theme ('pager', NULL, $limit, 0);
		return $output;
		
	}	
}


function kegiatanrevisisemua_main_form() {
	$form['formdata'] = array (
		'#type' => 'fieldset',
		'#title'=> 'Pilihan Data',
		'#collapsible' => TRUE,
		'#collapsed' => TRUE,        
	);
	$filter = arg(2);
	if (isset($filter) && ($filter=='filter')) {
		$kodeuk = arg(3);
		$sumberdana = arg(4);
		$statusinaktif = arg(5);
		$jenis = arg(6);
		$statusperubahan = arg(7);
		$kegiatan = arg(8);
		
		
	} else {
		$sumberdana = 'ZZ';
		$statusinaktif = 'ZZ';
		$jenis = 'ZZ';
		$statusperubahan = 'ZZ';
		$kodeuk = 'ZZ';
		$kegiatan = '';
	}

		   
	$pquery = "select kodedinas, kodeuk, namasingkat, namauk from {unitkerja} where aktif=1 order by kodedinas" ;
	$pres = db_query($pquery);
	$dinas = array();    
    
	$dinas['ZZ'] = 'SELURUH SKPD';
	//if (!isVerifikator()) $dinas['ZZ'] ='00000 - SEMUA SKPD';
	while ($data = db_fetch_object($pres)) {
		$dinas[$data->kodeuk] = $data->kodedinas . ' - ' . $data->namasingkat;
	}
		
	 
	$form['formdata']['kodeuk']= array(
		'#type'         => 'select', 
		'#title'        => 'SKPD',
		'#options'	=> $dinas,
		//'#description'  => 'kodeuktujuan', 
		//'#maxlength'    => 60, 
		'#width'         => 30, 
		//'#required'     => !$disabled, 
		//'#disabled'     => $disabled, 
		'#default_value'=> $kodeuk, 
		'#weight' => 2,
	);
	
	$pquery = "select sumberdana from {sumberdanalt} order by nomor" ;
	$pres = db_query($pquery);
	$sumberdanaotp = array();
	$sumberdanaotp['ZZ'] = '- SEMUA -';
	while ($data = db_fetch_object($pres)) {
		$sumberdanaotp[$data->sumberdana] = $data->sumberdana;
	}
	$form['formdata']['sumberdana']= array(
		'#type'         => 'select', 
		'#title'        => 'Sumber Dana', 
		'#options'		=> $sumberdanaotp,
		'#width'         => 30, 
		'#default_value'=> $sumberdana, 
		'#weight' => 4,
	);
	
	/*
	$form['formdata']['jenis']= array(
		'#type' => 'radios', 
		'#title' => t('Jenis'), 
		'#default_value' => $jenis,
		'#options' => array(	
			 'ZZ' => t('Semua'), 	
			 'gaji' => t('Gaji'), 	
			 'langsung' => t('Langsung'),
			 'ppkd' => t('PPKD'),	
		   ),
		'#weight' => 5,		
	);	
	*/
	$form['formdata']['jenis']= array(
		'#type' => 'select', 
		'#title' => t('Jenis'), 
		'#default_value' => $jenis,
		'#options' => array(	
			 'ZZ' => t('- SEMUA -'), 	
			 'gaji' => t('GAJI'), 	
			 'subsidi' => t('SUBSIDI'),
			 'hibah' => t('HIBAH'),
			 'bansos' => t('BANTUAN SOSIAL'),
			 'bagihasil' => t('BAGI HASIL'),
			 'bankeu' => t('BANTUAN KEUANGAN'),
			 'langsung' => t('LANGSUNG'),
			 'pegawai' => t('PEGAWAI'),
			 'barangjasa' => t('BARANG DAN JASA'),
			 'modal' => t('MODAL'),	
		   ),
		'#weight' => 5,		
	);		
	$form['formdata']['ss1'] = array (
		'#type' => 'item',
		'#value' => "<div style='clear:both;'></div>",
		'#weight' => 6,
	);		
	
	$form['formdata']['statusinaktif']= array(
		'#type' => 'radios', 
		'#title' => t('Status'), 
		'#default_value' => $statusinaktif,
		'#options' => array(	
			 'ZZ' => t('Semua'), 	
			 '0' => t('Aktif'),	
			 '1' => t('Inaktif'), 	
			 '2' => t('Melebihi Plafon'),
			 '3' => t('Kurang dari Plafon'),
		   ),
		'#weight' => 7,		
	);		

	$form['formdata']['ss2'] = array (
		'#type' => 'item',
		'#value' => "<div style='clear:both;'></div>",
		'#weight' => 8,
	);		
	
	$form['formdata']['statusperubahan']= array(
		'#type' => 'radios', 
		'#title' => t('Perubahan'), 
		'#default_value' => $statusperubahan,
		'#options' => array(	
			 'ZZ' => t('Semua'), 	
			 '0' => t('Tidak Ada'),	
			 '1' => t('Ada Perubahan'), 	
		   ),
		'#weight' => 9,		
	);		

	$form['formdata']['ss4'] = array (
		'#type' => 'item',
		'#value' => "<div style='clear:both;'></div>",
		'#weight' => 10,
	);		
	
	$form['formdata']['kegiatanx']= array(
		'#type' => 'textfield', 
		'#title' => t('Kegiatan'), 
		'#default_value' => $kegiatan,
		'#weight' => 11,		
	);		
	
	$form['formdata']['ss'] = array (
		'#type' => 'item',
		'#value' => "<div style='clear:both;'></div>",
		'#weight' => 12,
	);		
	$form['formdata']['submit'] = array (
		'#type' => 'submit',
		'#value' => 'Tampilkan',
		'#weight' => 13
	);
	
	return $form;
}
function kegiatanrevisisemua_main_form_submit($form, &$form_state) {
	$sumberdana = $form_state['values']['sumberdana'];
	$kodeuk = $form_state['values']['kodeuk'];
	$statusinaktif = $form_state['values']['statusinaktif'];
	$statusperubahan = $form_state['values']['statusperubahan'];
	$jenis = $form_state['values']['jenis'];
	$kegiatan = $form_state['values']['kegiatanx'];
	$tahun= $form_state['values']['tahun'];
	
	/*
	$_SESSION['sumberdana'] = $sumberdana;
	$_SESSION['statusinaktif'] = $statusinaktif;
	$_SESSION['jenis'] = $jenis;
	
	$_SESSION['kodeuk'] = $kodeuk;
	*/
	
	$uri = 'apbd/kegiatanperubahansemua/filter/' . $kodeuk . '/' . $sumberdana . '/' . $statusinaktif . '/' . $jenis . '/' . $statusperubahan . '/' . $kegiatan;
	drupal_goto($uri);
	
}

function kegiatanrevisisemua_transfer_form() {
	$form['formtransfer'] = array (
		'#type' => 'fieldset',
		'#title'=> 'Transfer Data Dari MUSRENBANGCAM',
		'#collapsible' => TRUE,
		'#collapsed' => TRUE,        
	);
	$pquery = "select kodeuk, namauk, namasingkat from {unitkerja} where aktif=1 and iskecamatan=1 order by namasingkat" ;
	$pres = db_query($pquery);
	$dinas = array();
	$kodeuk = apbd_getuseruk();
	$typekodeuk = 'select';
	if (!isSuperuser())
		$typekodeuk='hidden';
	//$dinas[''] = '--- pilih dinas teknis---';
	while ($data = db_fetch_object($pres)) {
		$dinas[$data->kodeuk] = $data->namasingkat;
	}
	
	$form['formtransfer']['kodeuk']= array(
		'#type'         => 'select', 
		//'#title'        => 'Kecamatan',
		'#options'	=> $dinas,
		//'#description'  => 'kodeuktujuan', 
		//'#maxlength'    => 60, 
		'#width'         => 20, 
		//'#required'     => !$disabled, 
		//'#disabled'     => $disabled, 
		'#default_value'=> $kodeuk,
		'#attributes'	=> array('style' => 'margin-left: 20px;'),
	); 
	
	

	$musrenbang = l("<div class='boxp' >MUSRENBANGCAM</div>", 'apbd/kegiatancam', array('html'=> true));
	$renja= l("<div class='boxp'>RENJA SKPD</div>", 'apbd/kegiatanskpd', array('html'=>true));
	$proses = "<div class='boxproses' id='boxproses'><a href='#transfercamskpd' class='btn_blue' style='color: white;'>---Transfer---></a></div>";
	$document = "<div style='height: 50px; text-align:center;'>$musrenbang $proses $renja<div style='clear:both;'></div></div>";
	$form['formtransfer']['keterangan'] = array (
		'#type' => 'markup',
		'#value' => $document,
		'#weight' => 1,
	);
	return $form;
}

function GenDataHeader($kodeuk) {
	
	if ($kodeuk!='ZZ') {
		$sql = "select namauk from {unitkerja} where kodeuk='" . $kodeuk . "'" ;
		$res = db_query($sql);
		if ($data = db_fetch_object($res)) {
			$skpd = ' ' . $data->namauk;
		}
	}
	
	$rowsjudul[] = array (array ('data'=>'DAFTAR KEGIATAN' . $skpd, 'width'=>'875px', 'colspan'=>'3', 'style' =>'border:none; font-weight:900; font-size:1.3em; text-align:center;'));
	$rowsjudul[] = array (array ('data'=>'', 'width'=>'535px', 'colspan'=>'3', 'style' =>'border:none; font-weight:900; font-size:1.3em; text-align:center;'));
	$rowsjudul[] = array (array ('data'=>'___________________________________________', 'width'=>'875px', 'colspan'=>'3', 'style' =>'border:none; font-weight:900; font-size:1.3em; text-align:center;'));
	$rowsjudul[] = array (array ('data'=>'', 'width'=>'535px', 'colspan'=>'3', 'style' =>'border:none; font-weight:900; font-size:1.3em; text-align:center;'));
	

	$opttbl = array('cellspacing'=>'1', 'cellpadding'=>'1', 'border' => '0');
	$headerkosong = array();

	//$output = theme_box('', apbd_theme_table($headerkosong, $rowsjudul, $opttbl));
	$output = theme_box('', apbd_theme_table($headerkosong, $rowsjudul, $opttbl));
	
	return $output;
	
}
function GenDataPrint($kodeuk, $fsql) {
	set_time_limit(0);
	ini_set('memory_limit', '640M');
	
	//drupal_set_message($fsql);
	
	$totalF =0;
	$totalA =0;
	$totalP =0;
	$headersrek[] = array (
						 
						 array('data' => 'No.',  'width'=> '25px', 'style' => 'border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Kegiatan',  'width' => '340px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Lokasi',  'width' => '100px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Sumberdana',  'width' => '90px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Penetapan',  'width' => '90px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Anggaran',  'width' => '90px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Plafon',  'width' => '90px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Ket',  'width' => '50px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 
					);


	$result = db_query($fsql . ' order by namasingkat,kegiatan');
	
	if ($result) {
		while ($data = db_fetch_object($result)) {
			$no += 1;
			
			$totalF += $data->plafon;
			$totalA += $data->anggaran;
			$totalP += $data->penetapan;
			
			$str_plafon='';					

			if ($data->inaktif) $str_plafon .= "*)";

			if ($data->dispensasi) $str_plafon .= "D";
			
			if ($kodeuk=='ZZ')
				$kegnama = $data->kegiatan . ' (' . $data->namasingkat . ')';
			else
				$kegnama = $data->kegiatan;

			if ($data->revisi=='1')
				$ket = 'Perub';
			else
				$ket = '';
			
			$rowsrek[] = array (
								 array('data' => $no,  'width'=> '25px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:right;'),
								 array('data' => $kegnama,  'width' => '340px', 'style' => ' border-right: 1px solid black; text-align:left;'),
								 array('data' => str_replace('||',', ', $data->lokasi), 'width' => '100px', 'align' => 'left', 'valign'=>'top', 'style' => ' border-right: 1px solid black; text-align:left;'),								 
								 array('data' => $data->sumberdana,  'width' => '90px', 'style' => ' border-right: 1px solid black; text-align:left;'),
								 array('data' => apbd_fn($data->penetapan),  'width' => '90px', 'style' => ' border-right: 1px solid black; text-align:right;'),
								 array('data' => apbd_fn($data->anggaran),  'width' => '90px', 'style' => ' border-right: 1px solid black; text-align:right;'),
								 array('data' => apbd_fn($data->plafon),  'width' => '90px', 'style' => ' border-right: 1px solid black; text-align:right;'),
								 array('data' => $ket,  'width' => '50px', 'style' => ' border-right: 1px solid black; text-align:left;'),
								 );				

		}
	}										 
								 			
	$rowsrek[] = array (
						 array('data' => '',  'width'=> '25px', 'style' => 'border-left: 1px solid black;  border-top: 2px solid black; border-bottom: 1px solid black; text-align:right;'),
						 array('data' => 'TOTAL',  'width' => '340px', 'style' => ' border-top: 2px solid black; border-bottom: 1px solid black; text-align:left;'),
						 array('data' => '',  'width' => '100px', 'style' => ' border-top: 2px solid black; border-bottom: 1px solid black; text-align:left;'),
						 array('data' => '',  'width' => '90px', 'style' => 'border-right: 1px solid black; border-top: 2px solid black; border-bottom: 1px solid black; text-align:left;'),
						 array('data' => apbd_fn($totalP),  'width' => '90px', 'style' => ' border-right: 1px solid black; border-top: 2px solid black; border-bottom: 1px solid black; text-align:right;'),
						 array('data' => apbd_fn($totalA),  'width' => '90px', 'style' => ' border-right: 1px solid black; border-top: 2px solid black; border-bottom: 1px solid black; text-align:right;'),
						 array('data' => apbd_fn($totalF),  'width' => '90px', 'style' => ' border-right: 1px solid black; border-top: 2px solid black; border-bottom: 1px solid black; text-align:right;'),
						 array('data' => '',  'width' => '50px', 'style' => ' border-right: 1px solid black; border-top: 2px solid black; border-bottom: 1px solid black; text-align:left;'),
						 );				

	
	$opttbl = array('cellspacing'=>'1', 'cellpadding'=>'1', 'border' => '1');
	$output = theme_box('', apbd_theme_table($headersrek, $rowsrek, $opttbl));
	
	return $output;	
}


function kegiatanrevisisemua_exportexcel($kodeuk) {
error_reporting(E_ALL);

set_time_limit(0);
ini_set('memory_limit', '640M');

ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
//date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once 'files/PHPExcel/Classes/PHPExcel.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Set document properties
$objPHPExcel->getProperties()->setCreator("SIPKD Anggaran Online")
							 ->setLastModifiedBy("SIPKD Anggaran Online")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Excel document generated from SIPKD Anggaran Online.")
							 ->setKeywords("office 2007 SIPKD Anggaran Online openxml php")
							 ->setCategory("SIPKD Anggaran Analisa");
// Add Header
$row = 1;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $row ,'Fungsi')
			->setCellValue('B' . $row ,'Urusan')
            ->setCellValue('C' . $row ,'SKPD')
            ->setCellValue('D' . $row ,'Program')
			->setCellValue('E' . $row ,'Kegiatan')
			->setCellValue('F' . $row ,'Akun Utama')
			->setCellValue('G' . $row ,'Akun Kelompok')
			->setCellValue('H' . $row ,'Akun Jenis')
			->setCellValue('I' . $row ,'Akun Obyek')
			->setCellValue('J' . $row ,'Akun Rincian')
			->setCellValue('K' . $row ,'Jumlah');

//Open data							 
//$customwhere = sprintf(' and tahun=%s ', variable_get('apbdtahun', 0));
if ($kodeuk!='ZZ') {
	$customwhere .= sprintf(' and kegiatanskpd.kodeuk=\'%s\' ', $kodeuk);	
}	
$where = ' where inaktif=0 ' . $customwhere;
	
$sql = "SELECT programurusanfungsi.namafungsi, programurusanfungsi.namaurusan, CONCAT_WS(' - ', unitkerja.kodedinas, unitkerja.namasingkat) skpd, programurusanfungsi.namaprogram, kegiatanskpd.kegiatan, rekeninglengkap.akunutama, rekeninglengkap.akunkelompok, rekeninglengkap.akunjenis, rekeninglengkap.akunobyek, rekeninglengkap.akunrincian,
anggperkeg.jumlah FROM unitkerja inner join kegiatanskpd ON unitkerja.kodeuk=kegiatanskpd.kodeuk INNER JOIN programurusanfungsi ON kegiatanskpd.kodepro=programurusanfungsi.kodepro INNER JOIN anggperkeg ON anggperkeg.kodekeg=kegiatanskpd.kodekeg INNER JOIN rekeninglengkap ON rekeninglengkap.kodero=anggperkeg.kodero " . $where;
$result = db_query($sql);
while ($data = db_fetch_object($result)) {
	$row++;
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A' . $row, $data->namafungsi)
				->setCellValue('B' . $row, $data->namaurusan)
				->setCellValue('C' . $row, $data->skpd)
				->setCellValue('D' . $row, $data->namaprogram)
				->setCellValue('E' . $row, $data->kegiatan)
				->setCellValue('F' . $row, $data->akunutama)
				->setCellValue('G' . $row, $data->akunkelompok)
				->setCellValue('H' . $row, $data->akunjenis)
				->setCellValue('I' . $row, $data->akunobyek)
				->setCellValue('J' . $row, $data->akunrincian)
				->setCellValue('K' . $row, $data->jumlah);
}
						

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Analisis Belanja SKPD');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
$fname = 'analisis_belanja_skpd_' . $kodeuk . '.xlsx';
header('Content-Disposition: attachment;filename=' . $fname);
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
}

?>