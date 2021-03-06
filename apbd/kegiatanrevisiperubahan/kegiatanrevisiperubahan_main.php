<?php
function kegiatanrevisiperubahan_main($arg=NULL, $nama=NULL) {
	/*
    $h = '<style>label{font-weight: bold; display: block; width: 150px; float: left;}</style>';
    drupal_set_html_head($h);
	drupal_add_css('files/css/kegiatancam.css');
	drupal_add_js('files/js/kegiatancam.js');
	*/
	$qlike='';
	$limit = 15;
	
	//drupal_set_message('x');
	
	$kodesuk = '';
	$tahun = variable_get('apbdtahun', 0);
	$ntitle = 'Belanja';
    if ($arg) {
		switch(arg(2)) {
			case 'show':
				//$qlike = " and lower(k.kegiatan) like lower('%%%s%%')";    
				$qlike = sprintf(" and lower(k.kegiatan) like lower('%%%s%%') ", db_escape_string(arg(3)));	
				//drupal_set_message(arg(4));
				break;
			case 'filter':
			
				//drupal_set_message('x-filter');
				
				$nntitle ='';
				$kodeuk = arg(3);
				$sumberdana = arg(4);
				$statusisi = arg(5);
				$kodesuk = arg(6);
				$statustw = arg(7);
				$statusinaktif = arg(8);
				$jenis = arg(9);
				$plafon = arg(10);
				$jenisrevisi = arg(11);
				
				$kegiatan = arg(12);
				$rekening = arg(13);
				//echo $rekening;
				$rincian = arg(14);
				//echo $rincian . '|';
				$exportpdf = arg(15); 
				
				//drupal_set_message('x : ' . $kodeuk);
				//drupal_set_message($exportpdf);
				

			default:
				$tahun = variable_get('apbdtahun', 0);
				$sumberdana = $_SESSION['sumberdana'];
				$statusisi = $_SESSION['statusisi'];
				$statustw = $_SESSION['statustw'];	
				$statusinaktif = $_SESSION['statusinaktif'];
				$jenis = $_SESSION['jenis'];
				$jenisrevisi = $_SESSION['jenisrevisi'];
				if ($jenisrevisi=='') $jenisrevisi = '0';
				$plafon = $_SESSION['plafon'];
				
				//drupal_set_message('x : ' . $kodeuk);
				if (isSuperuser() || isUserview() || isVerifikator()) {
					$kodeuk = $_SESSION['kodeuk'];
					if ($kodeuk == '') 	$kodeuk = 'ZZ';
					
					
				} else {
					$statusinaktif = '';
					$kodeuk = apbd_getuseruk();
					if (isUserKecamatan())
						$kodesuk = apbd_getusersuk();
					else
						$kodesuk = $_SESSION['kodesuk'];
				}				
				break;
		}
	} else {
		$tahun = variable_get('apbdtahun', 0);
		$sumberdana = $_SESSION['sumberdana'];
		$statusisi = $_SESSION['statusisi'];
		$statustw = $_SESSION['statustw'];	
		$statusinaktif = $_SESSION['statusinaktif'];
		$jenis = $_SESSION['jenis'];
		$jenisrevisi = $_SESSION['jenisrevisi'];
		if ($jenisrevisi=='') $jenisrevisi = '0';
		$plafon = $_SESSION['plafon'];
		
		//drupal_set_message('x : ' . $kodeuk);
		if (isSuperuser() || isUserview() || isVerifikator()) {
			$kodeuk = $_SESSION['kodeuk'];
			if ($kodeuk == '') 	$kodeuk = 'ZZ';
			
			
		} else {
			$statusinaktif = '';
			$kodeuk = apbd_getuseruk();
			if (isUserKecamatan())
				$kodesuk = apbd_getusersuk();
			else
				$kodesuk = $_SESSION['kodesuk'];
		}
		
	}
	
	if(isUserview()){
		$url='apbd/belanjadppa';
		drupal_goto($url);
	}
	if (isSuperuser() || isUserview() || isVerifikator()) {
		//$kodeuk = $_SESSION['kodeuk'];
		if ($kodeuk == '') 	$kodeuk = 'ZZ';
		
		
	} else {
		$kodeuk = apbd_getuseruk();
		if (isUserKecamatan())
			$kodesuk = apbd_getusersuk();
		else
			$kodesuk = $_SESSION['kodesuk'];
	}	
	if (isSuperuser() || isUserview()) {
		if ($kodeuk !='ZZ') {
			$qlike .= sprintf(' and k.kodeuk=\'%s\' ', $kodeuk);
			$pquery = sprintf("select kodeuk, namasingkat from {unitkerja} where kodeuk='%s'", db_escape_string($kodeuk));
			$presult = db_query($pquery);
			if ($data=db_fetch_object($presult)) {
				$ntitle .= ' ' . $data->namasingkat;
			}
		} 
		$adminok = true;
		
	} else if (isVerifikator()) {
		if ($kodeuk !='ZZ') {
			$qlike .= sprintf(' and k.kodeuk=\'%s\' ', $kodeuk);
			$pquery = sprintf("select kodeuk, namasingkat from {unitkerja} where kodeuk='%s'", db_escape_string($kodeuk));
			$presult = db_query($pquery);
			if ($data=db_fetch_object($presult)) {
				$ntitle .= ' ' . $data->namasingkat;
			}
		} 
		$adminok = true;
							
	} else {
		$qlike .= sprintf(' and k.plafon>0 and k.kodeuk=\'%s\' ', $kodeuk);
		if ($kodesuk != '') {
			$qlike .= sprintf(' and (k.kodesuk=\'%s\' ', $kodesuk);
			$qlike .= " or k.kodesuk='')";
		}
		
		$adminok = false;
	}

	//keg cari
	//if (strlen($kegcari)>0) {
	//	$qlike .= sprintf(" and lower(k.kegiatan) like lower('%%%s%%') ", db_escape_string($kegcari));
	//}
	
	//STATUS PENGISIAN
	if ($statusisi=='sudah') {
		$qlike .= sprintf(' and (k.total=k.plafon) and (k.plafon>0)');
	} elseif ($statusisi=='sebagian') {
		$qlike .= sprintf(' and (k.total>0) and (k.total<k.plafon) and (k.plafon>0) ');
	} elseif ($statusisi=='belum') {
		$qlike .= sprintf(' and (k.total=0 or k.total is null) and (k.plafon>0) ');
	} elseif ($statusisi=='lebih') {
		$qlike .= sprintf(' and (k.total>k.plafon) and (k.plafon>0) ');
	}

	//STATUS TW
	if ($statustw=='sudah') {
		$qlike .= sprintf(' and k.total>0 and (k.total=(k.tw1+k.tw2+k.tw3+k.tw4)) ');
	} elseif ($statustw=='belum') {
		$qlike .= sprintf(' and k.total>0 and (k.total>(k.tw1+k.tw2+k.tw3+k.tw4)) ');
	}
	
	/*
	'4' => t('Dalam Proses'),
	'5' => t('Disetujui'),
	'6' => t('Ditolak'),
	*/
	
	//STATUS INAKTIF
	if ($statusinaktif=='0') {
		$qlike .= sprintf(' and k.inaktif=0 ');
	} elseif ($statusinaktif=='1') {
		$qlike .= sprintf(' and (k.inaktif=1 or k.plafon=0) ');
	} elseif ($statusinaktif=='2') {
		$qlike .= sprintf(' and k.dispensasi=1 '); 
	} elseif ($statusinaktif=='3') {
		$sql_revisi_join = ' inner join {kegiatanrevisiperubahan} krp on k.kodekeg=krp.kodekeg ';
	} elseif ($statusinaktif=='4') {
		$sql_revisi_join = ' inner join {kegiatanrevisiperubahan} krp on k.kodekeg=krp.kodekeg ';
		$qlike .= ' and krp.status=0 ';
	} elseif ($statusinaktif=='5') {
		$sql_revisi_join = ' inner join {kegiatanrevisiperubahan} krp on k.kodekeg=krp.kodekeg ';
		$qlike .= ' and krp.status=1 ';
	} elseif ($statusinaktif=='6') {
		$sql_revisi_join = ' inner join {kegiatanrevisiperubahan} krp on k.kodekeg=krp.kodekeg ';
		$qlike .= ' and krp.status=9 ';
	} 
	
	//drupal_set_message($qlike);
	 
	//STATUS INAKTIF 
	if ($jenis=='gaji') {
		$qlike .= ' and k.jenis=1 and k.isppkd=0 ';
	} elseif ($jenis=='langsung') {
		$qlike .= ' and k.jenis=2 ';
	} elseif ($jenis=='ppkd') {
		$qlike .= ' and k.jenis=1 and k.isppkd=1 ';
	}
   
	if ($jenisrevisi !='0')  
		$qlike .= sprintf(' and (krp.jenisrevisi=%s) ', $jenisrevisi);
	
	 
	//SUMBER DANA
	if ($sumberdana != '') {
		$qlike .= sprintf(' and (k.sumberdana1=\'%s\'  or k.sumberdana2=\'%s\') ', $sumberdana, $sumberdana);
		$ntitle .= ' ' . $sumberdana;
	}
	
	//PLAFON
	$str_star = '';
	$sql_exceed = '';
	if ($plafon=='new') {
		$qlike .= ' and (k.periode=' . variable_get('apbdrevisi', 0) . ' and k.totalpenetapan=0) ';
	} elseif ($plafon=='up') {
		$qlike .= ' and k.plafon>k.totalpenetapan and k.totalpenetapan>0 ';
	} elseif ($plafon=='down') {
		$qlike .= ' and k.plafon<k.totalpenetapan ';
	} elseif ($plafon=='still') {
		$qlike .= ' and k.plafon=k.totalpenetapan ';
	} elseif ($plafon=='star') {
		$qlike .= sprintf(' and (keg.bintang=1) ');
		$str_star_join = ' inner join {kegiatanperubahan} keg on k.kodekeg=keg.kodekeg  ';
	} elseif ($plafon=='exceed') {
		$sql_exceed = ' and k.kodekeg in (select l.kodekeg from {lrakegrek} l inner join {anggperkegrevisi} a1 on l.kodekeg=a1.kodekeg and l.kodero=a1.kodero where a1.jumlah<l.realisasi)  ';
	}	
	
	//drupal_set_message($str_star_join);

	//NAMA KEGIATAN
	if (strlen($kegiatan)>0) {
		$qlike .= sprintf(" and lower(k.kegiatan) like lower('%%%s%%') ", db_escape_string($kegiatan));
	}
	
	
	//$output .= drupal_get_form('kegiatanrevisiperubahan_transfer_form');
	$output .= drupal_get_form('kegiatanrevisiperubahan_main_form');
	
	drupal_set_title($ntitle);	
	
	if (isVerifikator()) {
		global $user;
		$username =  $user->name;		
		
		$sql_revisi_join = ' inner join {kegiatanrevisiperubahan} krp on k.kodekeg=krp.kodekeg ';
		$sql_v_join = 'inner join {userskpd} us on k.kodeuk=us.kodeuk ';
		$qlike .= sprintf(' and us.username=\'%s\' ', $username);
		
		//$str_tabel_keg = 'kegiatanverifikator';
		$str_tabel_keg = 'kegiatanrevisi';
		
	} else {
		$str_tabel_keg = 'kegiatanrevisi';
		
		//KOREKSI
		//if (!isSuperuser()) {
			$sql_revisi_join = ' inner join {kegiatanrevisiperubahan} krp on k.kodekeg=krp.kodekeg ';
		//}
	}
	
	//drupal_set_message($rekening);
	if ((strlen($rekening) == 0) and (strlen($rincian) ==0)) {
		
		//,krp.status,krp.jawaban,krp.id
		
		$where = ' where true' . $customwhere . $qlike . $sql_exceed;
		
		$fsql = "select distinct k.kodekeg, k.periode, k.nomorkeg,k.tahun,k.kodepro,k.kodeuk,k.kegiatan,k.jenis, k.lokasi,k.programtarget,k.total, k.plafon, k.totalpenetapan, k.plafonpenetapan, u.namasingkat, k.isppkd,  k.adminok, k.sumberdana1 sumberdana, k.inaktif,k.dispensasi, krp.status,krp.jawaban,krp.id from {" . $str_tabel_keg . "} k left join {unitkerja} u on ( k.kodeuk=u.kodeuk) left join {program} p on (k.kodepro = p.kodepro) " . $sql_revisi_join . $sql_v_join . $str_star_join . $where;
		//$fsql = sprintf($sql, addslashes($nama));
		
		$fcountsql = "select count(distinct k.kodekeg) as cnt from {" . $str_tabel_keg . "} k " . $sql_revisi_join . $sql_v_join  . $str_star_join . $where;

		
	} else if ((strlen($rekening) > 0) and (strlen($rincian) ==0)) {
		$qlike .= sprintf(" and lower(a.uraian) like lower('%%%s%%') ", db_escape_string($rekening));
		$where = ' where true' . $customwhere . $qlike . $sql_exceed;

		$fsql = "select distinct k.kodekeg,k.periode, k.nomorkeg,k.tahun,k.kodepro,k.kodeuk,k.kegiatan,k.jenis, k.lokasi,k.programtarget,k.total, k.plafon, k.totalpenetapan, k.plafonpenetapan, u.namasingkat, k.isppkd,  k.adminok, k.sumberdana1 sumberdana, k.inaktif,k.dispensasi, krp.status,krp.jawaban,krp.id from {" . $str_tabel_keg . "} k left join {unitkerja} u on ( k.kodeuk=u.kodeuk) left join {program} p on (k.kodepro = p.kodepro) inner join {anggperkegrevisi} a on k.kodekeg=a.kodekeg " . $sql_revisi_join . $sql_v_join . $str_star_join . $where;

		drupal_set_message($fsql);
		$fcountsql = "select count(distinct k.kodekeg) as cnt from {" . $str_tabel_keg . "} k inner join {anggperkegrevisi} a on k.kodekeg=a.kodekeg " . $sql_revisi_join . $sql_v_join  . $str_star_join . $where;			

		
	} else if ((strlen($rekening) == 0) and (strlen($rincian) >0)) {
		$qlike .= sprintf(" and lower(d.uraian) like lower('%%%s%%') ", db_escape_string($rincian));
		$where = ' where true' . $customwhere . $qlike  . $sql_exceed;

		$fsql = "select distinct k.kodekeg,k.periode, k.nomorkeg,k.tahun,k.kodepro,k.kodeuk,k.kegiatan,k.jenis, k.lokasi,k.programtarget,k.total, k.plafon, k.totalpenetapan, k.plafonpenetapan, u.namasingkat, k.isppkd, k.adminok, k.sumberdana1 sumberdana, k.inaktif,k.dispensasi, krp.status,krp.jawaban,krp.id from {" . $str_tabel_keg . "} k left join {unitkerja} u on ( k.kodeuk=u.kodeuk) left join {program} p on (k.kodepro = p.kodepro) inner join {anggperkegdetilrevisi} d on k.kodekeg=d.kodekeg " . $sql_revisi_join . $sql_v_join . $str_star_join . $where;

		//drupal_set_message($fsql);
		$fcountsql = "select count(distinct k.kodekeg) as cnt from {" . $str_tabel_keg . "} k inner join {anggperkegrevisi} a on k.kodekeg=a.kodekeg inner join {anggperkegdetilrevisi} d on a.kodekeg=d.kodekeg and a.kodero=d.kodero " . $sql_revisi_join . $sql_v_join  . $str_star_join . $where;

		
	} else {
		$qlike .= sprintf(" and lower(a.uraian) like lower('%%%s%%') and lower(d.uraian) like lower('%%%s%%') ", db_escape_string($rekening), db_escape_string($rincian));
		$where = ' where true' . $customwhere . $qlike  . $sql_exceed;

		$fsql = "select distinct k.kodekeg,k.periode, k.nomorkeg,k.tahun,k.kodepro,k.kodeuk,k.kegiatan,k.jenis, k.lokasi,k.programtarget,k.total, k.plafon, k.totalpenetapan, k.plafonpenetapan, u.namasingkat, k.isppkd,  k.adminok, k.sumberdana1 sumberdana, k.inaktif,k.dispensasi, krp.status,krp.jawaban,krp.id from {" . $str_tabel_keg . "} k left join {unitkerja} u on ( k.kodeuk=u.kodeuk) left join {program} p on (k.kodepro = p.kodepro) inner join {anggperkegrevisi} a on k.kodekeg=a.kodekeg inner join {anggperkegdetilrevisi} d on k.kodekeg=d.kodekeg " . $sql_revisi_join .  $sql_v_join . $str_star_join . $where;

		//echo $fsql;
		$fcountsql = "select count(distinct k.kodekeg) as cnt from {" . $str_tabel_keg . "} k inner join {anggperkegrevisi} a on k.kodekeg=a.kodekeg inner join {anggperkegdetilrevisi} d on a.kodekeg=d.kodekeg and a.kodero=d.kodero " . $sql_revisi_join . $sql_v_join  . $str_star_join . $where;

	}

	//drupal_set_message($fcountsql);
	if (isset($exportpdf))   {
		if ($exportpdf=='pdf') {
			$pdfFile = 'Daftar_Kegiatan_Perbahan_Dicari.pdf';
			
			$htmlHeader = GenDataHeader($kodeuk);
			$htmlContent = GenDataPrint($kodeuk, $fsql);
			
			apbd_ExportPDF2('L', 'F4', $htmlHeader, $htmlContent, $pdfFile);
			//return $htmlContent;
			
		} else if ($exportpdf=='xls') { 
			
			//kegiatanrevisiperubahan_exportexcel($fsql);
			kegiatanrevisiperubahan_exportexcel_rekening_rekap();
		
		} else {
			
			//kegiatanrevisiperubahan_exportexcel_all();
			kegiatanrevisiperubahan_exportexcel_rekening();
		}	
		
	} else {
		$output = GenDataView($kodeuk , $sumberdana , $statusisi , $kodesuk , $statustw , 
				$statusinaktif , $jenis , $jenisrevisi, $kegiatan , $rekening , $rincian, $plafon, $fsql, $fcountsql);
		return $output;
	}
    
}

function GenDataView($kodeuk , $sumberdana , $statusisi , $kodesuk , $statustw , 
				$statusinaktif , $jenis , $jenisrevisi, $kegiatan , $rekening , $rincian, $plafon, $fsql, $fcountsql) {
    $h = '<style>label{font-weight: bold; display: block; width: 150px; float: left;}</style>';
    drupal_set_html_head($h);
	drupal_add_css('files/css/kegiatancam.css');
	drupal_add_js('files/js/kegiatancam.js');
	$limit = 15;
	
	//if ($kodeuk=='08') drupal_set_message($fsql);
	
	//drupal_set_message($fsql);
	//$output .= drupal_get_form('kegiatanrevisiperubahan_transfer_form');
	$output .= drupal_get_form('kegiatanrevisiperubahan_main_form');
	if (isSuperuser()  || isVerifikator()) {
		$header = array (
			array('data' => 'No', 'width' => '10px', 'valign'=>'top'),
			array('data' => '', 'width' => '5px', 'valign'=>'top'),
			array('data' => '', 'width' => '5px', 'valign'=>'top'),
			array('data' => 'SKPD', 'field'=> 'namasingkat', 'valign'=>'top'),
			array('data' => 'Kegiatan', 'field'=> 'kegiatan', 'valign'=>'top'),
			array('data' => 'Plafon', 'field'=> 'total','width' => '90px', 'valign'=>'top'),
			array('data' => 'Penetapan', 'field'=> 'totalpenetapan','width' => '90px', 'valign'=>'top'),
			array('data' => 'Realisasi', 'width' => '90px', 'valign'=>'top'),
			array('data' => 'Perubahan', 'field'=> 'total','width' => '90px', 'valign'=>'top'),
			array('data' => 'Vrf/Appv', 'width' => '75px', 'valign'=>'top'),
			array('data' => 'Keterangan', 'width' => '75px', 'valign'=>'top'),
			array('data' => '', 'width' => '40px', 'valign'=>'top'),
		);
	}
	
	else { 
		$header = array (
			array('data' => 'No', 'width' => '10px', 'valign'=>'top'),
			array('data' => '', 'width' => '5px', 'valign'=>'top'),
			array('data' => '', 'width' => '5px', 'valign'=>'top'),
			array('data' => 'Kegiatan', 'field'=> 'kegiatan', 'valign'=>'top'),
			array('data' => 'Plafon', 'field'=> 'total','width' => '90px', 'valign'=>'top'),
			array('data' => 'Penetapan', 'field'=> 'totalpenetapan','width' => '90px', 'valign'=>'top'),
			array('data' => 'Realisasi', 'width' => '90px', 'valign'=>'top'),
			array('data' => 'Perubahan', 'field'=> 'total','width' => '90px', 'valign'=>'top'),
			array('data' => 'Vrf/Appv', 'width' => '75px', 'valign'=>'top'),
			array('data' => 'Keterangan', 'width' => '75px', 'valign'=>'top'),
			array('data' => '', 'width' => '40px', 'valign'=>'top'),
		);
	}
	$tablesort = tablesort_sql($header);
    if ($tablesort=='') {
        $tablesort=' order by kodekeg';
    }
	
	//drupal_set_message($fsql);
	
	$result = pager_query($fsql . $tablesort, $limit, 0, $fcountsql);
	
	//Jam,Menit,Detik,Bulan,Hari,Tahun
	//$batas = mktime(20, 0, 0, 6, 16, 2015) ;
	//$sekarang = time () ;
	//$selisih =($batas-$sekarang) ;
	$allowedit = (batastgl() || (isSuperuser()));
	
	//CEK TAHUN
	//$allowedit = ($allowedit and ($tahun == variable_get('apbdtahun', 0)));
    
    $no=0;
    $page = $_GET['page'];
    if (isset($page)) {
        $no = $page * $limit;
    } else {
        $no = 0; 
    }
	
	if (isVerifikator()) {
		global $user;
		$username = $user->name;		
	} 
	
	$periode = variable_get('apbdrevisi', 0);  
    if ($result) {
        while ($data = db_fetch_object($result)) {

			$editlink = '';
			
			$jawabanpersetujuan = '';
			$jawaban = '';
			$ada_revisi = false;
			$status_revisi = '0';
			/*
			$sql = 'select kodekeg,status,jawaban,id from {kegiatanrevisiperubahan} where kodekeg=\'%s\'';
			$res_cek = db_query(db_rewrite_sql($sql), array ($data->kodekeg));
			if ($res_cek) {
				if ($data_cek = db_fetch_object($res_cek)) {
			*/
			$ada_revisi = true;
			$status_revisi = $data->status;
			
			if  (isSuperuser()) $jawabanpersetujuan = $data->jawaban;
			$id = $data->id;

			
			//PERUBAHAN
			$kegname = l($data->kegiatan, 'apbd/kegiatanrevisiperubahan/edit/' . $data->kodekeg  . '/' . $data->id, array('html' =>TRUE));
			//Revisi
			//$kegname = l($data->kegiatan, 'apbd/kegiatanrevisi/edit/' . $data->kodekeg . '/' . $data->id , array('html' =>TRUE));
			
			//if ($ada_revisi) {
				//if (!isVerifikator())
				//	$kegname .= '<font color="red">*</font>'; 
				
				$sql = 'select username,jawaban from {kegiatanverifikasi} where kodekeg=\'%s\'';
				$res_cek = db_query(db_rewrite_sql($sql), array ($data->kodekeg));
				if ($res_cek) {
					while ($data_cek = db_fetch_object($res_cek)) {
						if ($data_cek->jawaban !='') {
							//$jawabanpersetujuan .= '<p><font color="Chocolate">' . $data_cek->username . ': ' . $data_cek->jawaban . '</font></p>';
							$jawabanpersetujuan .= '<p><font color="Chocolate">' . $data_cek->jawaban . '</font></p>';
						}
					}
				}			
				
				//CATATAN Rekening
				//CATATAN VERIFIKATOR
				$sqlrek = sprintf("select username,jawaban from {anggperkegrevisiverifikasi} where kodekeg='%s'", db_escape_string($data->kodekeg));
				$resrek = db_query($sqlrek);	
				while ($datarek = db_fetch_object($resrek)) {
					//$jawabanpersetujuan .= "<font color='red'>" . $datarek->username . ": " . $datarek->jawaban . "; </font>";
					$jawabanpersetujuan .= "<font color='red'>" . $datarek->jawaban . "; </font>";
				}		
			
			//}
			
			//EDIT REVISI
			//'apbd/kegiatanrevisi/editperubahan/0/' . $kodeuk . '/' . $kodekeg ;
			if (($allowedit) or isSuperuser()) $editlink = l('Edit', 'apbd/kegiatanrevisi/editperubahan/' . $id, array('html'=>TRUE)) . "&nbsp;";
			
			if ($data->total==0) {
				//$editlink =l('Rekening', 'apbd/kegiatanskpdperubahan/rekening/edit/' . $data->kodekeg, array('html'=>TRUE));
				//$editlink .= l('Rek', 'apbdkegrekeningrevisi/' . $data->kodekeg , array('html'=>TRUE));
				$editlink .= l('Rek', 'apbd/kegiatanrevisiperubahan/rekening/' . $data->kodekeg  . '/0', array('html'=>TRUE));

			} else {
				//$editlink =l('Rekening', 'apbd/kegiatanskpdperubahan/rekening/' . $data->kodekeg, array('html'=>TRUE));
				$editlink .= l('Rek', 'apbd/kegiatanrevisiperubahan/rekening/' . $data->kodekeg  . '/0', array('html'=>TRUE));
				
				/*				
				if ($ada_revisi) {
					$sql = 'select count(*) x from {anggperkegrevisiverifikasi} where kodekeg=\'%s\'';
					$res_cek = db_query(db_rewrite_sql($sql), array ($data->kodekeg));
					if ($res_cek) {
						if ($data_cek = db_fetch_object($res_cek)) {
							if ($data_cek->x >0) {
								$editlink .= l('<font color="Chocolate">Rek</font>', 'apbd/kegiatanrevisiperubahan/rekening/' . $data->kodekeg  . '/0', array('html'=>TRUE));
								
							}
						}
					}		
				}
				*/
			}
			
			//REALISASI
			$realisasi = 0;
			
			
			$sql_r = sprintf("select sum(realisasi) sumrea from {lrakegrek} where kodekeg='%s'", db_escape_string($data->kodekeg));
			$res_r = db_query($sql_r);
			if ($res_r) {

				if ($data_r = db_fetch_object($res_r)) {
					$realisasi = $data_r->sumrea;
				}
			} 
			
			
			
			//TW
			$editlink .= "&nbsp;" . l('TW', 'apbd/kegiatanrevisiperubahan/triwulan/' . $data->kodekeg . '/0', array('html'=>TRUE));
			
			if (($data->totalpenetapan==0) and ($data->periode==$periode))
				$penetapan_ada = false;
			else
				$penetapan_ada = true;
			
			if (isSuperuser()) {
				$editlink .= "&nbsp;" . l('Admin', 'apbd/kegiatanrevisiperubahan/editadmin/' . $data->kodekeg, array('html'=>TRUE));
				/*
				if ($penetapan_ada)
					$editlink .= "&nbsp;" . 'Hapus';
				else
					$editlink .= "&nbsp;" . l('Hapus', 'apbd/kegiatanrevisiperubahan/delete/' . $data->kodekeg , array('html'=>TRUE));
				//$editlink .= "&nbsp;" . l('Cetak', 'apbd/kegiatanskpd/printperubahan/' . $data->kodekeg . '/10/dpa' , array('html'=>TRUE)) ;
				*/
			} 
			//HAPUS
			if (user_access('kegiatanskpd penghapusan')) {
				if (($data->status==0) or ($data->status==999))
					$editlink .= "&nbsp;" . l('Hapus', 'apbd/kegiatanrevisi/delete/' . $data->id, array('html'=>TRUE))  . "&nbsp;";		 
				else 
					$editlink .= "&nbsp;" . 'Hapus'  . "&nbsp;";
			}
			
			
			if (isVerifikator()) {
				if ($ada_revisi) {
					$editlink .= "&nbsp;" . l('Verifikasi', 'apbdverifikasi/' . $data->kodekeg, array('html'=>TRUE)) ;
				}
			} else {
					//CETAK
					$editlink .= "&nbsp;" . l('Cetak', 'apbd/kegiatanskpd/printusulan/' . $data->id . '/10/rka' , array('html'=>TRUE)) ;
					//$editlink .= "&nbsp;" . 'Verifikasi';
			}
			if ($data->inaktif) 
				$str_info = "<img src='/files/inaktif.png'>";
			
			else {
				if ($data->total > $data->plafon) 
					$str_info = "<img src='/files/limit.gif'>";
				else if ($data->total == $data->plafon) 
					$str_info = "<img src='/files/icon-finished.png'>";
				else {

					if ($data->dispensasi)
						$str_info = "<img src='/files/revisi16.jpg'>";
					else
						$str_info = "<img src='/files/icon-unfinished.png'>";
				}
			}
			
			//group1.png
			
			if ($data->totalpenetapan==$data->plafon)
				$str_plafon = "<img src='/files/icon-still.png'>";
			else if ($data->totalpenetapan>$data->plafon)
				$str_plafon = "<img src='/files/icon-down.png'>";
			else
				if ($penetapan_ada) 
					$str_plafon = "<img src='/files/icon-up.png'>";
				else
					$str_plafon = "<img src='/files/icon-new.png'>";
			

			//VERIFIKASI
			$num_ver = 0;
			$str_ver = '';
			$sql_r = sprintf("select username,persetujuan from {kegiatanverifikasi} where kodekeg='%s'", db_escape_string($data->kodekeg));
			$res_r = db_query($sql_r);
			while ($data_r = db_fetch_object($res_r)) {
				$num_ver ++;
				if ($data_r->persetujuan)
					$str_ver .= "<img src='/files/verify/fer_ok.png' title='" . $data_r->username . "'>";
				else
					$str_ver .= "<img src='/files/verify/fer_no.png' title='" . $data_r->username . "'>";

				if ($username==$data_r->username) $kegname .= '<font color="red">**</font>';				
			} 
			for ($x = $num_ver+1; $x <= 3; $x++) {
				$str_ver .= "<img src='/files/verify/fer_belum.png'>";
			}
			
			//Persetujuan
			if (!$ada_revisi) 
				$str_ver .= "<img src='/files/verify/fer_belum.png'>";
			else {
				if ($status_revisi==0)
					$str_ver .= "<img src='/files/icon/edit.png' title='Dalam proses'>";
				elseif ($status_revisi==1)
					$str_ver .= "<img src='/files/icon/cek.png' title='Disetujui'>";
				elseif ($status_revisi==9)
					$str_ver .= "<img src='/files/icon/stop.png' title='Ditolak'>";
					//$str_ver .= "<img src='/files/icon/edit.png' title='Dalam proses'>";
				else
					$str_ver .= "<img src='/files/icon/info.png'>";
			}	
			
			//BINTANG
			$str_agg = '';
			if ($penetapan_ada) {
				$sql_r = sprintf("select anggaran,bintang from {kegiatanperubahan} where kodekeg='%s'", db_escape_string($data->kodekeg));
				$res_r = db_query($sql_r);
				if ($data_r = db_fetch_object($res_r)) {
					if ($data_r->bintang == '1') {
						$str_agg = '<p><font color="Red">' . apbd_fn($data_r->anggaran) . '</font></p>';
						$str_info = "<img src='/files/bintang.png'>";
					} 				
				}

			}
			
            $no++;			
			if (isSuperuser() || isVerifikator()) { 
				
				$rows[] = array (
					array('data' => $no, 'align' => 'right', 'valign'=>'top'),
					array('data' => $str_plafon, 'align' => 'center', 'valign'=>'top'),
					array('data' => $str_info, 'align' => 'center', 'valign'=>'top'),
					array('data' => $data->namasingkat, 'align' => 'left', 'valign'=>'top'),
					array('data' => $kegname, 'align' => 'left', 'valign'=>'top'),
					//array('data' => $data->programtarget, 'align' => 'left', 'valign'=>'top'),
					//array('data' => str_replace('||',', ', $data->lokasi), 'align' => 'left', 'valign'=>'top'),
					//array('data' => $data->sumberdana, 'align' => 'left', 'valign'=>'top'),
					array('data' => apbd_fn($data->plafon), 'align' => 'right', 'valign'=>'top'),
					array('data' => apbd_fn($data->totalpenetapan) . $str_agg, 'align' => 'right', 'valign'=>'top'),
					array('data' => apbd_fn($realisasi), 'align' => 'right', 'valign'=>'top'),
					array('data' => apbd_fn($data->total), 'align' => 'right', 'valign'=>'top'),
					array('data' => $str_ver, 'align' => 'left', 'valign'=>'top'),
					array('data' => $jawabanpersetujuan, 'align' => 'left', 'valign'=>'top'),
					array('data' => $editlink, 'align' => 'left', 'valign'=>'top'),
				);
			} else {
				$rows[] = array (
					array('data' => $no, 'align' => 'right', 'valign'=>'top'),
					array('data' => $str_plafon, 'align' => 'center', 'valign'=>'top'),
					array('data' => $str_info, 'align' => 'center', 'valign'=>'top'),
					array('data' => $kegname, 'align' => 'left', 'valign'=>'top'),
					//array('data' => $data->programtarget, 'align' => 'left', 'valign'=>'top'),
					//array('data' => str_replace('||',', ', $data->lokasi), 'align' => 'left', 'valign'=>'top'),
					//array('data' => $data->sumberdana, 'align' => 'left', 'valign'=>'top'),
					array('data' => apbd_fn($data->plafon), 'align' => 'right', 'valign'=>'top'),
					array('data' => apbd_fn($data->totalpenetapan), 'align' => 'right', 'valign'=>'top'),
					array('data' => apbd_fn($realisasi), 'align' => 'right', 'valign'=>'top'),
					array('data' => apbd_fn($data->total), 'align' => 'right', 'valign'=>'top'),
					array('data' => $str_ver, 'align' => 'left', 'valign'=>'top'),
					array('data' => $jawabanpersetujuan, 'align' => 'left', 'valign'=>'top'),
					array('data' => $editlink, 'align' => 'left', 'valign'=>'top'),
				);
			}
		}
    } else {
        $rows[] = array (
            array('data' => 'data kosong, silahkan menambahkan', 'colspan'=>'3')
        );
    }
	$btn = "";

 
	$status = 0;
	$record = 0;

	if (isSuperuser()) {
		
		//$btn .= l('Usulan Revisi', 'apbd/kegiatanrevisi/edit1/', array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;'))) . "&nbsp;";
		$btn .= l('Kegiatan Baru', 'apbd/kegiatanrevisiperubahan/editadmin/new/' . $kodeuk, array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;'))) . "&nbsp;" ;

	} 
	
	else {
		//if ($allowedit) {
	
			$btn .= l('Usulan Perubahan', 'apbd/kegiatanpilih/0/' . $kodeuk .'/4/0/0/0/0/0/0/0/0/0/0/0/0', array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;'))) . "&nbsp;";
			//$btn .= l('Kegiatan Baru', 'apbd/kegiatanrevisiperubahan/editadmin', array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;'))) . "&nbsp;" ;
				
		//}
	}	
	
	
	//$btn .= l('Cetak', 'apbd/laporan/rka/rekapaggbelanja/' . $kodeuk , array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;')));
	          //$uri = 'apbd/kegiatanrevisiperubahan/filter/' . $kodeuk . '/' . $sumberdana . '/' . $statusisi . '/' . $kodesuk . '/'. $statustw . '/' . $statusinaktif . '/' . $jenis . '/' .$plafon . '/' . $jenisrevisi;	
	$btn .= l('Cetak', 'apbd/kegiatanrevisiperubahan/filter/' . $kodeuk . '/' . $sumberdana . '/' . $statusisi . '/' . $kodesuk . '/'. $statustw . '/' . $statusinaktif . '/' . $jenis  . '/' . $plafon  . '/' . $jenisrevisi . '/' . $kegiatan . '/' . $rekening . '/' . $rincian . '/pdf' , array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;')));
	
    $btn .= "&nbsp;" . l("Cari", 'apbd/kegiatanrevisiperubahan/find/' , array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;'))) ;
	
	if (isSuperuser()) {
		
		$btn .= "&nbsp;" . l('Excel', 'apbd/kegiatanrevisiperubahan/filter/' . $kodeuk . '/' . $sumberdana . '/' . $statusisi . '/' . $kodesuk . '/'. $statustw . '/' . $statusinaktif . '/' . $jenis  . '/' . $plafon  . '/' . $jenisrevisi . '/' . $kegiatan . '/' . $rekening . '/' . $rincian . '/xls' , array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;')));
		$btn .= "&nbsp;" . l('Analisis', 'apbd/kegiatanrevisiperubahan/filter/' . $kodeuk . '/' . $sumberdana . '/' . $statusisi . '/' . $kodesuk . '/'. $statustw . '/' . $statusinaktif . '/' . $jenis  . '/' . $plafon  . '/' . $jenisrevisi . '/' . $kegiatan . '/' . $rekening . '/' . $rincian . '/xlsa' , array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;')));
		
		//$btn .= "&nbsp;" . l('Rekening Invalid', 'node/295' , array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;')));
		
		$btn .= "&nbsp;" . l('Semua Kegiatan', 'apbd/kegiatanperubahansemua' , array ('html' => true, 'attributes'=> array ('class'=>'btn_blue', 'style'=>'color:white;')));

		if ($kodeuk!='ZZ')
			$btn .= "&nbsp;" . l('Persetujuan', 'revisipersetujuan/' . $kodeuk . '/' . $jenisrevisi , array ('html' => true, 'attributes'=> array ('class'=>'btn_green', 'style'=>'color:white;')));
	}
	
    $output .= $btn . theme_box('', theme_table($header, $rows)) . $btn;

	
	//    $output .= theme_box('', theme_table($header, $rows));
//	if (user_access('kegiatanskpd tambah'))
//		$output .= l("<img src='/files/button-add.png' title='Tambah data baru'>", 'apbd/kegiatanrevisiperubahan/edit/' , array('html'=>TRUE)) ;
//	if (user_access('kegiatanskpd pencarian'))		
//        $output .= l("<img src='/files/button-search.png' title='Pencarian data'>", 'apbd/kegiatanrevisiperubahan/find/' , array('html'=>TRUE)) ;
    $output .= theme ('pager', NULL, $limit, 0);
    return $output;
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
	$totalP =0;
	$totalR =0;
	$headersrek[] = array (
						 
						 array('data' => 'No.',  'width'=> '25px', 'style' => 'border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Kegiatan',  'width' => '175px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Target',  'width' => '165px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Lokasi',  'width' => '100px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Sumberdana',  'width' => '90px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Plafon',  'width' => '90px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Penetapan',  'width' => '90px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Perubahan',  'width' => '90px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'Ket',  'width' => '50px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 
					);


	$result = db_query($fsql);
	
	if ($result) {
		while ($data = db_fetch_object($result)) {
			$no += 1;
			
			$totalF += $data->plafon;
			$totalP += $data->totalpenetapan;
			$totalR += $data->total;
			
			$str_plafon='';					
			if ($data->plafonpenetapan==$data->plafon)
				$str_plafon = "Tetap; ";
			else if ($data->plafonpenetapan>$data->plafon)
				$str_plafon = "Turun; ";
			
			else
				if ($data->totalpenetapan == 0)
					$str_plafon = "Baru; ";
				else
					$str_plafon = "Naik; ";

			if ($data->inaktif) 
				$str_plafon .= "*)";
			
			else {
				if ($data->total > $data->plafon) 
					$str_plafon .= "L";
				else {

					if ($data->dispensasi) $str_plafon .= "D";
				}
			}
			
			if ($kodeuk=='ZZ')
				$kegnama = $data->kegiatan . ' (' . $data->namasingkat . ')';
			else
				$kegnama = $data->kegiatan;
			
			$rowsrek[] = array (
								 array('data' => $no,  'width'=> '25px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:right;'),
								 array('data' => $kegnama,  'width' => '175px', 'style' => ' border-right: 1px solid black; text-align:left;'),
								 array('data' => $data->programtarget, 'width' => '165px', 'align' => 'left', 'valign'=>'top', 'style' => ' border-right: 1px solid black; text-align:left;'),
								 array('data' => str_replace('||',', ', $data->lokasi), 'width' => '100px', 'align' => 'left', 'valign'=>'top', 'style' => ' border-right: 1px solid black; text-align:left;'),								 
								 array('data' => $data->sumberdana,  'width' => '90px', 'style' => ' border-right: 1px solid black; text-align:left;'),
								 array('data' => apbd_fn($data->plafon),  'width' => '90px', 'style' => ' border-right: 1px solid black; text-align:right;'),
								 array('data' => apbd_fn($data->totalpenetapan),  'width' => '90px', 'style' => ' border-right: 1px solid black; text-align:right;'),
								 array('data' => apbd_fn($data->total),  'width' => '90px', 'style' => ' border-right: 1px solid black; text-align:right;'),
								 array('data' => $str_plafon,  'width' => '50px', 'style' => ' border-right: 1px solid black; text-align:left;'),
								 );				

		}
	}										 
								 			
	$rowsrek[] = array (
						 array('data' => '',  'width'=> '25px', 'style' => 'border-left: 1px solid black;  border-top: 2px solid black; border-bottom: 1px solid black; text-align:right;'),
						 array('data' => 'TOTAL',  'width' => '175px', 'style' => ' border-top: 2px solid black; border-bottom: 1px solid black; text-align:left;'),
						 array('data' => '',  'width' => '165px', 'style' => ' border-top: 2px solid black; border-bottom: 1px solid black; text-align:left;'),
						 array('data' => '',  'width' => '100px', 'style' => ' border-top: 2px solid black; border-bottom: 1px solid black; text-align:left;'),
						 array('data' => '',  'width' => '90px', 'style' => 'border-right: 1px solid black; border-top: 2px solid black; border-bottom: 1px solid black; text-align:left;'),
						 array('data' => apbd_fn($totalF),  'width' => '90px', 'style' => ' border-right: 1px solid black; border-top: 2px solid black; border-bottom: 1px solid black; text-align:right;'),
						 array('data' => apbd_fn($totalP),  'width' => '90px', 'style' => ' border-right: 1px solid black; border-top: 2px solid black; border-bottom: 1px solid black; text-align:right;'),
						 array('data' => apbd_fn($totalR),  'width' => '90px', 'style' => ' border-right: 1px solid black; border-top: 2px solid black; border-bottom: 1px solid black; text-align:right;'),
						 array('data' => '',  'width' => '50px', 'style' => ' border-right: 1px solid black; border-top: 2px solid black; border-bottom: 1px solid black; text-align:left;'),
						 );				

	
	$opttbl = array('cellspacing'=>'1', 'cellpadding'=>'1', 'border' => '1');
	$output = theme_box('', apbd_theme_table($headersrek, $rowsrek, $opttbl));
	
	return $output;	
}

					
function kegiatanrevisiperubahan_main_form() {
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
		$statusisi = arg(5);
		$kodesuk = arg(6);
		$statustw = arg(7);
		$statusinaktif = arg(8);
		$jenis = arg(9);
		$plafon = arg(10);
		$jenisrevisi = arg(11);
		
	} else {
		$sumberdana = $_SESSION['sumberdana'];
		$statusisi = $_SESSION['statusisi'];
		$statustw = $_SESSION['statustw'];	
		$statusinaktif = $_SESSION['statusinaktif'];	
		$jenis = $_SESSION['jenis'];	
		$jenisrevisi = $_SESSION['jenisrevisi'];
		$plafon = $_SESSION['plafon'];

		if (isSuperuser() || isUserview() || isVerifikator()) 
			$kodeuk = $_SESSION['kodeuk'];
		else
			$kodesuk = $_SESSION['kodesuk'];
	}
	if ($jenisrevisi=='') $jenisrevisi = '0';
	//drupal_set_message($filter);

	//if (isset($kodeuk)) {
	//    $form['formdata']['#collapsed'] = TRUE;
	//    //if (isUserKecamatan())
	//    //    if ($kodeuk != apbd_getuseruk())
	//    //        $form['formdata']['#collapsed'] = FALSE;
	//}
		   
	if (isSuperuser()) {
		$pquery = "select kodedinas, kodeuk, namasingkat, namauk from {unitkerja} where aktif=1 order by kodedinas" ;
		$pres = db_query($pquery);
		$dinas = array();        
		
		$dinas['ZZ'] ='00000 - SEMUA SKPD';
		while ($data = db_fetch_object($pres)) {
			$dinas[$data->kodeuk] = $data->kodedinas . ' - ' . $data->namasingkat;
		}
		
		$typeuk='select';
		$typesuk='hidden';
	
	} else if (isVerifikator()) {

		if (isVerifikator()) {
			global $user;
			$username =  $user->name;		
			
			$where .= sprintf(' and us.username=\'%s\' ', $username);
		}
	
		$pquery = "select kodedinas, kodeuk, namasingkat, namauk from {unitkerja} where aktif=1 and kodeuk in (select k.kodeuk from {kegiatanrevisiperubahan} k inner join {userskpd} us on k.kodeuk=us.kodeuk " . $where . ") order by kodedinas" ;
		$pres = db_query($pquery);
		$dinas = array();        
		
		$dinas['ZZ'] ='00000 - SEMUA SKPD';
		while ($data = db_fetch_object($pres)) {
			$dinas[$data->kodeuk] = $data->kodedinas . ' - ' . $data->namasingkat;
		}
		
		$typeuk='select';
		$typesuk='hidden';
		
	} else {
		$typeuk = 'hidden';
		$kodeuk = apbd_getuseruk();
		
		$typesuk ='select';

		$qlike .= sprintf(' and k.kodeuk=\'%s\' ', $kodeuk);
		$pquery = sprintf('select kodesuk, namasuk from {subunitkerja} where kodeuk=\'%s\' order by kodesuk', $kodeuk);
		
		//drupal_set_message($pquery);
		
		$pres = db_query($pquery);
		$subskpd = array();
		$subskpd[''] = '- Pilih Bidang -';
		while ($data = db_fetch_object($pres)) {
			$subskpd[$data->kodesuk] = $data->namasuk;
		}

		if (isUserKecamatan()) {
			$typesuk='hidden';
			$kodesuk = apbd_getusersuk();
		} else
			$typesuk='select';
	}
	 
	$form['formdata']['kodeuk']= array(
		'#type'         => $typeuk, 
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

	$form['formdata']['kodesuk']= array(
		'#type'         => $typesuk, 
		'#title'        => 'Bidang/Bagian',
		'#options'		=> $subskpd,
		//'#description'  => 'kodesuk', 
		//'#maxlength'    => 60, 
		//'#size'         => 20, 
		//'#required'     => !$disabled, 
		//'#disabled'     => $disabled, 
		'#default_value'=> $kodesuk, 
		'#weight' => 3,
	); 

	$form['formdata']['jenisrevisi']= array(
		'#type' => 'radios', 
		'#title' => t('Jenis Revisi'), 
		'#default_value' => $jenisrevisi,
		'#options' => array(	
			 '0' => t('Semua'), 	
			 '1' => t('[1] Pergeseran'), 	
			 '2' => t('[2] Administrasi'), 	
			 '3' => t('[3] Dana Transfer'),	
			 '4' => t('[4] Darurat'),	
		   ),
		'#weight' => 4,		
	);
	
	$pquery = "select sumberdana from {sumberdanalt} order by nomor" ;
	$pres = db_query($pquery);
	$sumberdanaotp = array();
	$sumberdanaotp[''] = '- SEMUA -';
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
	$form['formdata']['ssjxx'] = array (
		'#type' => 'item',
		'#value' => "<div style='clear:both;'></div>",
		'#weight' => 5,
	);
	 
	$form['formdata']['jenis']= array(
		'#type' => 'radios', 
		'#title' => t('Jenis'), 
		'#default_value' => $jenis,
		'#options' => array(	
			 '' => t('Semua'), 	
			 'gaji' => t('Gaji'), 	
			 'langsung' => t('Langsung'),
			 'ppkd' => t('PPKD'),	
		   ),
		'#weight' => 5,		
	);	
	
	$form['formdata']['ssj'] = array (
		'#type' => 'item',
		'#value' => "<div style='clear:both;'></div>",
		'#weight' => 5,
	);		
 	
	$form['formdata']['statusisi']= array(
		'#type' => 'radios', 
		'#title' => t('Pengisian'), 
		'#default_value' => $statusisi,
		'#options' => array(	
			 '' => t('Semua'), 	
			 'sudah' => t('Selesai'), 	
			 'sebagian' => t('Sebagian'),
			 'belum' => t('Belum'),	
			 'lebih' => t('Lebih Plafon'),	
		   ),
		'#weight' => 6,		
	);	

	$form['formdata']['ss1'] = array (
		'#type' => 'item',
		'#value' => "<div style='clear:both;'></div>",
		'#weight' => 7,
	);		

	$form['formdata']['plafon']= array(
		'#type' => 'radios', 
		'#title' => t('Alokasi Anggaran'), 
		'#default_value' => $plafon,
		'#options' => array(	
			 '' => t('Semua'), 	
			 'new' => t('Usulan Baru'), 	
			 'up' => t('Naik'),
			 'down' => t('Turun'),	
			 'still' => t('Tetap'),	
			 'star' => t('Bintang'),
			 'exceed' => t('Dibawah Realisasi'),			 
		   ),
		'#weight' => 8,		
	);	

	$form['formdata']['ss1p'] = array (
		'#type' => 'item',
		'#value' => "<div style='clear:both;'></div>",
		'#weight' => 9,
	);		
	$form['formdata']['statustw']= array(
		'#type' => 'radios', 
		'#title' => t('Tri Wulan'), 
		'#default_value' => $statustw,
		'#options' => array(	
			 '' => t('Semua'), 	
			 'sudah' => t('Sudah'), 	
			 'belum' => t('Belum'),	
		   ),
		'#weight' => 10,		
	);		
	$form['formdata']['ss2'] = array (
		'#type' => 'item',
		'#value' => "<div style='clear:both;'></div>",
		'#weight' => 11,
	);		
	
	if (isSuperuser() || isUserview()  || isVerifikator()) {
		$statusinaktiftype = 'radios';
	} else {
		$statusinaktiftype = 'hidden';
		$statusinaktif = '';
	}
	
	$form['formdata']['statusinaktif']= array(
		'#type' => $statusinaktiftype, 
		'#title' => t('Status'), 
		'#default_value' => $statusinaktif,
		'#options' => array(	
			 '' => t('Semua'), 	
			 '0' => t('Aktif'),	
			 '1' => t('Inaktif'), 	
			 '2' => t('Perpanjang'),
			 '3' => t('Ada Perubahan'),
			 '4' => t('Dalam Proses'),
			 '5' => t('Disetujui'),
			 '6' => t('Ditolak'),
		   ),
		'#weight' => 12,		
	);		
	
	$form['formdata']['ss'] = array (
		'#type' => 'item',
		'#value' => "<div style='clear:both;'></div>",
		'#weight' => 13,
	);		
	$form['formdata']['submit'] = array (
		'#type' => 'submit',
		'#value' => 'Tampilkan',
		'#weight' => 14
	);
	
	return $form;
}
function kegiatanrevisiperubahan_main_form_submit($form, &$form_state) {
	$sumberdana = $form_state['values']['sumberdana'];
	$kodeuk = $form_state['values']['kodeuk'];
	$kodesuk = $form_state['values']['kodesuk'];
	$statusisi = $form_state['values']['statusisi'];
	$statustw = $form_state['values']['statustw'];
	$statusinaktif = $form_state['values']['statusinaktif'];
	$jenis = $form_state['values']['jenis'];
	$jenisrevisi = $form_state['values']['jenisrevisi'];
	$plafon = $form_state['values']['plafon'];
	
	$tahun= $form_state['values']['tahun'];

	$_SESSION['sumberdana'] = $sumberdana;
	$_SESSION['statusisi'] = $statusisi;
	$_SESSION['statustw'] = $statustw;
	$_SESSION['statusinaktif'] = $statusinaktif;
	$_SESSION['jenis'] = $jenis;
	$_SESSION['jenisrevisi'] = $jenisrevisi;
	$_SESSION['plafon'] = $plafon;
	
	if (isSuperuser() || isUserview()  || isVerifikator()) 
		$_SESSION['kodeuk'] = $kodeuk; 
	else
		$_SESSION['kodesuk'] = $kodesuk;
	
	$uri = 'apbd/kegiatanrevisiperubahan/filter/' . $kodeuk . '/' . $sumberdana . '/' . $statusisi . '/' . $kodesuk . '/'. $statustw . '/' . $statusinaktif . '/' . $jenis . '/' .$plafon . '/' . $jenisrevisi;

	
	drupal_goto($uri);
	
}


function kegiatanrevisiperubahan_exportexcel($fsql) {
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
            ->setCellValue('A' . $row ,'No')
			->setCellValue('B' . $row ,'SKPD')
			->setCellValue('C' . $row ,'Kegiatan')
			->setCellValue('D' . $row ,'Target')
			->setCellValue('E' . $row ,'Sumberdana')
			->setCellValue('F' . $row ,'Plafon')
			->setCellValue('G' . $row ,'Penetapan')
			->setCellValue('H' . $row ,'Realisasi')
			->setCellValue('I' . $row ,'Perubahan');

$result = db_query($fsql);
while ($data = db_fetch_object($result)) {
	$row++;
	
	//penetapan
	$sql_pl = "select total from {kegiatanskpd} where kodekeg='" . $data->kodekeg . "'";
	//$sql_pl = "select plafon,total from {kegiatanperubahan2} where kodekeg='" . $data->kodekeg . "'";
	$res_pl = db_query($sql_pl);
	if ($data_pl=db_fetch_object($res_pl)) {
		$total_lama = $data_pl->total;
	} else {
		$total_lama = 0;
	}
	

	//REALISASI
	$realisasi = 0;
	$sql_r = sprintf("select sum(realisasi) sumrea from {lrakegrek} where kodekeg='%s'", db_escape_string($data->kodekeg));
	$res_r = db_query($sql_r);
	if ($data_r = db_fetch_object($res_r)) {
		$realisasi = $data_r->sumrea;
	}
	else {
		$realisasi = 0;
	}
	if ($realisasi=='') $realisasi = 0;
	
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A' . $row, $row-1)
				->setCellValue('B' . $row, $data->namasingkat)
				->setCellValue('C' . $row, $data->kegiatan)
				->setCellValue('D' . $row, $data->programtarget)
				->setCellValue('E' . $row, $data->sumberdana)
				->setCellValue('F' . $row, $data->plafon)
				->setCellValue('G' . $row, $total_lama)
				->setCellValue('H' . $row, $realisasi)
				->setCellValue('I' . $row, $data->total)
;
}
						

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('DAFTAR KEGIATAN PERUBAHAN');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
$fname = 'daftar_kegiatan_perubahan.xlsx';
header('Content-Disposition: attachment;filename=' . $fname);
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
}

function kegiatanrevisiperubahan_exportexcel_all() {
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
            ->setCellValue('A' . $row ,'No')
			->setCellValue('B' . $row ,'SKPD')
			->setCellValue('C' . $row ,'Urusan')
			->setCellValue('D' . $row ,'Jenis')
			->setCellValue('E' . $row ,'Program')
			->setCellValue('F' . $row ,'Kegiatan')
			->setCellValue('G' . $row ,'Sumberdana')
			->setCellValue('H' . $row ,'Penetapan')
			->setCellValue('I' . $row ,'Plafon Revisi')
			->setCellValue('J' . $row ,'Anggaran Revisi')
			->setCellValue('K' . $row ,'Plafon  Perubahan')
			->setCellValue('L' . $row ,'Anggaran Perubahan');

$fsql = 'select kegiatanrevisi.kodekeg, unitkerja.namasingkat, urusan.urusan, program.program, kegiatanrevisi.kegiatan, kegiatanrevisi.jenis, kegiatanrevisi.plafonpenetapan, kegiatanrevisi.totalpenetapan, kegiatanrevisi.plafon, kegiatanrevisi.sumberdana1, kegiatanrevisi.total from unitkerja inner join kegiatanrevisi on unitkerja.kodeuk=kegiatanrevisi.kodeuk inner join program on program.kodepro=kegiatanrevisi.kodepro inner join urusan on program.kodeu=urusan.kodeu where kegiatanrevisi.inaktif=0 order by unitkerja.namasingkat, urusan.urusan, program.program';
$result = db_query($fsql);
while ($data = db_fetch_object($result)) {
	$row++;

	$sql_pl = "select total from {kegiatanskpd} where kodekeg='" . $data->kodekeg . "'";
	//$sql_pl = "select plafon,total from {kegiatanperubahan2} where kodekeg='" . $data->kodekeg . "'";
	$total_lama = 0;
	$res_pl = db_query($sql_pl);
	if ($data_pl=db_fetch_object($res_pl)) {
		$total_lama = $data_pl->total;
	}
	
	if ($data->jenis=='1') {
		$str_jenis = 'BTL';

		$sql_pl = "select left(kodero,3) kodej from {anggperkegrevisi} where kodekeg='" . $data->kodekeg . "'";
		$res_pl = db_query($sql_pl);
		if ($data_pl=db_fetch_object($res_pl)) {
			if ($data_pl->kodej=='514')
				$str_jenis = 'Hibah';
			else if ($data_pl->kodej=='515')
				$str_jenis = 'Bansos';
			else if ($data_pl->kodej=='516')
				$str_jenis = 'Bagihasil';
			else if ($data_pl->kodej=='517')
				$str_jenis = 'Bankeu';
			else if ($data_pl->kodej=='518')
				$str_jenis = 'Darurat';
			else
				$str_jenis = 'BTL';
		}
		
		
	} else
		$str_jenis = 'BL';
	
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A' . $row, $row-1)
				->setCellValue('B' . $row, $data->namasingkat)
				->setCellValue('C' . $row, $data->urusan)
				->setCellValue('D' . $row, $str_jenis)
				->setCellValue('E' . $row, $data->program)
				->setCellValue('F' . $row, $data->kegiatan)
				->setCellValue('G' . $row, $data->sumberdana1)
				->setCellValue('H' . $row, $total_lama)
				->setCellValue('I' . $row, $data->plafonpenetapan)
				->setCellValue('J' . $row, $data->totalpenetapan)
				->setCellValue('K' . $row, $data->plafon)
				->setCellValue('L' . $row, $data->total)
;
}
						

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('DAFTAR KEGIATAN PERUBAHAN');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
$fname = 'analisis_kegiatan_perubahan.xlsx';
header('Content-Disposition: attachment;filename=' . $fname);
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
}


function kegiatanrevisiperubahan_exportexcel_rekening() {
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
            ->setCellValue('A' . $row ,'SKPD')
			->setCellValue('B' . $row ,'Kegiatan')
			->setCellValue('C' . $row ,'Jenis')
			->setCellValue('D' . $row ,'Obyek')
			->setCellValue('E' . $row ,'Rekening')
			->setCellValue('F' . $row ,'Penetapan')
			->setCellValue('G' . $row ,'Realisasi')
			->setCellValue('H' . $row ,'Perubahan');

//$fsql = 'select kegiatanrevisi.kodekeg, unitkerja.namasingkat, kegiatanrevisi.kegiatan, concat(jenis.kodej, ' - ', jenis.uraian) as jenis, concat(obyek.kodeo, ' - ', obyek.uraian) as obyek, concat(anggperkegrevisi.kodero, ' - ', rincianobyek.uraian) as rekening, anggperkegrevisi.kodero, anggperkegrevisi.jumlah from unitkerja inner join kegiatanrevisi on unitkerja.kodeuk=kegiatanrevisi.kodeuk inner join anggperkegrevisi on anggperkegrevisi.kodekeg=kegiatanrevisi.kodekeg inner join rincianobyek on anggperkegrevisi.kodero=rincianobyek.kodero inner join obyek on left(anggperkegrevisi.kodero, 5) =obyek.kodeo inner join jenis on left(anggperkegrevisi.kodero,3)=jenis.kodej order by unitkerja.namasingkat, kegiatanrevisi.kegiatan';

$fsql = "select kegiatanrevisi.kodekeg, unitkerja.namasingkat, kegiatanrevisi.kegiatan, concat(anggperkegrevisi.kodero, ' - ', rincianobyek.uraian) as rekening, anggperkegrevisi.kodero, anggperkegrevisi.jumlah from unitkerja inner join kegiatanrevisi on unitkerja.kodeuk=kegiatanrevisi.kodeuk inner join anggperkegrevisi on anggperkegrevisi.kodekeg=kegiatanrevisi.kodekeg inner join rincianobyek on anggperkegrevisi.kodero=rincianobyek.kodero order by unitkerja.namasingkat, kegiatanrevisi.kegiatan";
$result = db_query($fsql);
while ($data = db_fetch_object($result)) {
	$row++;
	
	//$sql_pl = "select kodej, uraian from {jenis} where kodej='" . substr($data->kodero,0,3) . "'";
	$sql_pl = sprintf("select kodej, uraian from {jenis} where kodej='%s'", db_escape_string(substr($data->kodero,0,3)));
	$res_pl = db_query($sql_pl);
	if ($data_pl=db_fetch_object($res_pl)) {
		$jenis = $data_pl->kodej . ' - ' . $data_pl->uraian;
	}
	//$sql_pl = "select kodeo, uraian from {obyek} where kodeo='" . substr($data->kodero,0,5) . "'";
	$sql_pl = sprintf("select kodeo, uraian from {obyek} where kodeo='%s'", db_escape_string(substr($data->kodero,0,5)));
	$res_pl = db_query($sql_pl);
	if ($data_pl=db_fetch_object($res_pl)) {
		$obyek = $data_pl->kodeo . ' - ' . $data_pl->uraian;
	}
	
	//$sql_pl = "select jumlah from {anggperkeg} where kodekeg='" . $data->kodekeg . "' and kodero='" . $data->kodero . "'";
	$sql_pl = sprintf("select jumlah from {anggperkeg} where kodekeg='%s' and kodero='%s'", db_escape_string($data->kodekeg), db_escape_string($data->kodero));
	$total_lama = 0;
	$res_pl = db_query($sql_pl);
	if ($data_pl=db_fetch_object($res_pl)) {
		$total_lama = $data_pl->jumlah;
	}
	
	//REALISASI
	$realisasi = 0;
	$sql_r = sprintf("select realisasi sumrea from {lrakegrek} where kodekeg='%s' and kodero='%s'", db_escape_string($data->kodekeg), db_escape_string($data->kodero));
	$res_r = db_query($sql_r);
	if ($data_r = db_fetch_object($res_r)) {
		$realisasi = $data_r->sumrea;
	}
	else {
		$realisasi = 0;
	}
	
	
	drupal_set_message($data->rekening);
	
	if ($total_lama=='') $total_lama = 0;	
	if ($realisasi=='') $realisasi = 0;	
	
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A' . $row, $data->namasingkat)
				->setCellValue('B' . $row, $data->kegiatan)
				->setCellValue('C' . $row, $jenis)
				->setCellValue('D' . $row, $obyek)
				->setCellValue('E' . $row, $data->rekening)
				->setCellValue('F' . $row, $total_lama)
				->setCellValue('G' . $row, $realisasi)
				->setCellValue('H' . $row, $data->jumlah);
}
						

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('REKAP USULAN PERUBAHAN');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
$fname = 'analisis_kegiatan_perubahan.xlsx';
header('Content-Disposition: attachment;filename=' . $fname);
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
}

function kegiatanrevisiperubahan_exportexcel_rekening_rekap() {
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
			->setCellValue('D' . $row ,'Akun')
			->setCellValue('E' . $row ,'Kelompok')
			->setCellValue('F' . $row ,'Jenis')
			->setCellValue('G' . $row ,'Penetapan')
			->setCellValue('H' . $row ,'Perubahan');

//$fsql = 'select kegiatanrevisi.kodekeg, unitkerja.namasingkat, kegiatanrevisi.kegiatan, concat(jenis.kodej, ' - ', jenis.uraian) as jenis, concat(obyek.kodeo, ' - ', obyek.uraian) as obyek, concat(anggperkegrevisi.kodero, ' - ', rincianobyek.uraian) as rekening, anggperkegrevisi.kodero, anggperkegrevisi.jumlah from unitkerja inner join kegiatanrevisi on unitkerja.kodeuk=kegiatanrevisi.kodeuk inner join anggperkegrevisi on anggperkegrevisi.kodekeg=kegiatanrevisi.kodekeg inner join rincianobyek on anggperkegrevisi.kodero=rincianobyek.kodero inner join obyek on left(anggperkegrevisi.kodero, 5) =obyek.kodeo inner join jenis on left(anggperkegrevisi.kodero,3)=jenis.kodej order by unitkerja.namasingkat, kegiatanrevisi.kegiatan';

$fsql = "SELECT programurusanfungsi.namafungsi, programurusanfungsi.namaurusan, unitkerja.namasingkat, rekeninglengkap.akunutama, rekeninglengkap.akunkelompok, rekeninglengkap.akunjenis, sum(anggperkegperubahan.jumlah) as penetapan, sum(anggperkegperubahan.jumlahp) as perubahan FROM unitkerja inner join kegiatanperubahan ON unitkerja.kodeuk=kegiatanperubahan.kodeuk INNER JOIN programurusanfungsi ON kegiatanperubahan.kodepro=programurusanfungsi.kodepro INNER JOIN anggperkegperubahan ON anggperkegperubahan.kodekeg=kegiatanperubahan.kodekeg INNER JOIN rekeninglengkap ON rekeninglengkap.kodero=anggperkegperubahan.kodero WHERE kegiatanperubahan.inaktif=0 GROUP BY programurusanfungsi.namafungsi, programurusanfungsi.namaurusan, unitkerja.namasingkat, rekeninglengkap.akunutama, rekeninglengkap.akunkelompok, rekeninglengkap.akunjenis";
$result = db_query($fsql);
while ($data = db_fetch_object($result)) {
	$row++;
	
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A' . $row, $data->namafungsi)
				->setCellValue('B' . $row, $data->namaurusan)
				->setCellValue('C' . $row, $data->namasingkat)
				->setCellValue('D' . $row, $data->akunutama)
				->setCellValue('E' . $row, $data->akunkelompok)
				->setCellValue('F' . $row, $data->akunjenis)
				->setCellValue('G' . $row, $data->penetapan)
				->setCellValue('H' . $row, $data->perubahan);
}
						

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('REKAP USULAN PERUBAHAN');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
$fname = 'analisis_kegiatan_perubahan.xlsx';
header('Content-Disposition: attachment;filename=' . $fname);
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
}

function genReportBelanjaSKPD($jenis) {
	set_time_limit(0);
	ini_set('memory_limit', '640M');
	
	$headersrek[] = array (
						 array('data' => 'KODE',  'width'=> '60px','style' => 'border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black; border-top: 1px solid black; text-align:center;'),
						 array('data' => 'URAIAN', 'width' => '600px', 'colspan'=>'5',  'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 1px solid black; text-align:center;'),
						 array('data' => 'KEGIATAN', 'width' => '90px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 1px solid black; text-align:center;'),
						 array('data' => 'JUMLAH (Rp)',  'width' => '125px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 1px solid black; text-align:center;'),
						 );

	$where = ' where k.kodeuk=\'%s\'';
	
	$total=0;
	$sql = 'select mid(k.kodero,1,2) kodek,x.uraian,sum(jumlah) jumlahx from {anggperkeg} k left join {kelompok} x on mid(k.kodero,1,2)=x.kodek inner join {kegiatanskpd} keg on k.kodekeg=keg.kodekeg where keg.inaktif=0 and keg.jenis=' . $jenis;
	$fsql = $sql;
	$fsql .= ' group by mid(k.kodero,1,2),x.uraian order by mid(k.kodero,1,2)';

	//drupal_set_message( $fsql);
	$resultkel = db_query($fsql);
	if ($resultkel) {
		while ($datakel = db_fetch_object($resultkel)) {
			//$total += $datakel->jumlahx;
			$total= $datakel->jumlahx;
			$totalp += ($datakel->jumlahxp- $datakel->jumlahx);
			
			$rowsrek[] = array (
								 array('data' => $datakel->kodek,  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;'),
								 array('data' => $datakel->uraian, 'width' => '600px', 'colspan'=>'5',  'style' => ' border-right: 1px solid black; text-align:left;font-weight:bold;'),
								 array('data' => '', 'width' => '90px',  'style' => ' border-right: 1px solid black; text-align:left;'),
								 array('data' => apbd_fn($datakel->jumlahx),  'width' => '125px', 'style' => ' border-right: 1px solid black; border-bottom: 3px solid black; text-align:right;font-weight:bold;'),
								 );


			//JENIS
			$sql = 'select mid(k.kodero,1,3) kodej,j.uraian,sum(jumlah) jumlahx from {anggperkeg} k left join {jenis} j on mid(k.kodero,1,3)=j.kodej  inner join {kegiatanskpd} keg on k.kodekeg=keg.kodekeg where keg.inaktif=0 and mid(k.kodero,1,2)=\'%s\'';
			$fsql = sprintf($sql, db_escape_string($datakel->kodek));
			$fsql .= ' group by mid(k.kodero,1,3),j.uraian order by mid(k.kodero,1,3)';				

			//drupal_set_message( $fsql);
			$resultjenis = db_query($fsql);
			if ($resultjenis) {
				while ($datajenis = db_fetch_object($resultjenis)) {
					
					$rowsrek[] = array (
									 array('data' => ($datajenis->kodej),  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;'),
									 array('data' => $datajenis->uraian, 'width' => '600px', 'colspan'=>'5',  'style' => ' border-right: 1px solid black; text-align:left;'),
									 array('data' => '', 'width' => '90px',  'style' => ' border-right: 1px solid black; text-align:left;'),
									 array('data' => apbd_fn($datajenis->jumlahx),  'width' => '125px', 'style' => ' border-right: 1px solid black; border-bottom: 2px solid black; text-align:right;font-weight:bold;'),

									 );

					//OBYEK
					$sql = 'select mid(k.kodero,1,5) kodeo,j.uraian,sum(jumlah) jumlahx from {anggperkeg} k left join {obyek} j on mid(k.kodero,1,5)=j.kodeo  inner join {kegiatanskpd} keg on k.kodekeg=keg.kodekeg where keg.inaktif=0 and mid(k.kodero,1,3)=\'%s\'';
					$fsql = sprintf($sql, db_escape_string($datajenis->kodej));
					$fsql .= ' group by mid(k.kodero,1,5),j.uraian order by j.kodeo';
				
					$resultobyek = db_query($fsql);
					if ($resultobyek) {
						while ($dataobyek = db_fetch_object($resultobyek)) {
							
							
							$rowsrek[] = array (
										 array('data' => apbd_format_rek_obyek($dataobyek->kodeo),  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;'),
										 array('data' => $dataobyek->uraian, 'width' => '600px', 'colspan'=>'5',  'style' => ' border-right: 1px solid black; text-align:left;'),
										 array('data' => '', 'width' => '90px',  'style' => ' border-right: 1px solid black; text-align:left;'),
										 array('data' => apbd_fn($dataobyek->jumlahx),  'width' => '125px', 'style' => ' border-right: 1px solid black; text-align:right;font-weight:bold;'),
										 );		 
												 
							//REKENING
							$sql = 'select k.kodero,r.uraian,sum(jumlah) jumlahx from {anggperkeg} k left join {rincianobyek} r on k.kodero=r.kodero  inner join {kegiatanskpd} keg on k.kodekeg=keg.kodekeg where keg.inaktif=0 and  left(k.kodero,5)=\'%s\'';
							$fsql = sprintf($sql, db_escape_string($dataobyek->kodeo));
							$fsql .= ' group by k.kodero,r.uraian order by k.kodero';
								
							
							//drupal_set_message( $fsql);
							$result= db_query($fsql);
							if ($result) {
								while ($data = db_fetch_object($result)) {
									
									//font-style: italic;
									$rowsrek[] = array (
													 array('data' => apbd_format_rek_rincianobyek($data->kodero),  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;'),
													 array('data' => $data->uraian, 'width' => '600px', 'colspan'=>'5',  'style' => ' border-right: 1px solid black; text-align:left;'),
													 array('data' => '', 'width' => '90px',  'style' => ' border-right: 1px solid black; text-align:left;'),
													 array('data' => apbd_fn($data->jumlahx),  'width' => '125px', 'style' => ' border-right: 1px solid black; text-align:right;'),
													 );	
													 
									//DETIL SKPD 
									$no = 0;
									$sql = 'select u.kodeuk, u.namauk,sum(k.jumlah) jumlahx from {anggperkeg} k  inner join {kegiatanskpd} keg on k.kodekeg=keg.kodekeg inner join {unitkerja} u on keg.kodeuk=u.kodeuk where keg.inaktif=0 and k.kodero=\'%s\'';
									$fsql = sprintf($sql, db_escape_string($data->kodero));
									$fsql .= ' group by u.kodeuk, u.namauk order by sum(k.jumlah) desc'; 
									//drupal_set_message($fsql);
									$resdetil= db_query($fsql);
									if ($resdetil) {
										while ($datadetil = db_fetch_object($resdetil)) {
											$no++;
											
											$numkeg = 0;
											$sql = 'select count(kodekeg) numkeg from {kegiatanskpd} where kodeuk=\'%s\' and inaktif=0 and kodekeg in (select kodekeg from {anggperkeg} where kodero=\'%s\')';
											$fsql = sprintf($sql, db_escape_string($datadetil->kodeuk), db_escape_string($data->kodero));
											$reskeg = db_query($fsql);
											if ($reskeg) {
												if ($datakeg = db_fetch_object($reskeg)) {
													$numkeg = $datakeg->numkeg;
													}
											}	

											$rowsrek[] = array (
															 array('data' => '',  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;'),
															 array('data' => $no . '.',  'width'=> '50px', 'style' => 'text-align:right;'),
															 array('data' => $datadetil->namauk, 'width' => '550px', 'colspan'=>'4',  'style' => ' border-right: 1px solid black; text-align:left;font-style: italic;'),
															 array('data' => $numkeg, 'width' => '90px',  'style' => ' border-right: 1px solid black; text-align:right;'),
															 array('data' => apbd_fn($datadetil->jumlahx),  'width' => '125px', 'style' => ' border-right: 1px solid black; text-align:right;font-style: italic;'),
														);												
										}	//end detil skpd		
									}
									
								}	//end rekening
								
							}												 
						
						}	//end obyek
					}
				}
			}										 
								 
		////////
		}
	}	
	
	$rowsrek[] = array (
						 array('data' => 'TOTAL BELANJA',  'width'=> '750px',  'colspan'=>'7',  'style' => 'border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black; border-top: 1px solid black; text-align:right; font-weight:bold;'),
						 array('data' => apbd_fn($total),  'width' => '125px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 1px solid black; text-align:right; font-weight:bold;'),

						 );	
						 
	

	
						 
	$opttbl = array('cellspacing'=>'1', 'cellpadding'=>'1', 'border' => '0');

	$output .= theme_box('', apbd_theme_table($headersrek, $rowsrek, $opttbl));
	
	return $output;
	
}


?>