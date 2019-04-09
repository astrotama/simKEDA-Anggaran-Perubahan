<?php
function kegiatanrevisi_main($arg=NULL, $nama=NULL) {
    $h = '<style>label{font-weight: bold; display: block; width: 150px; float: left;}</style>';
    drupal_set_html_head($h);
	drupal_add_css('files/css/kegiatancam.css');
	drupal_add_js('files/js/kegiatancam.js');
	$qlike='';
	$limit = 15;
	//$status = 0;
	$tahun = variable_get('apbdtahun', 0);
	$ntitle = 'Revisi Kegiatan';
	$isinvalid = false;
	$jenisrevisi = '0';
	$status = '100';
    if ($arg) {
		switch($arg) {
			case 'show':
				//sprintf(' and (k.jenisrevisi=\'%s\') ', $jenisrevisi);			
				//$qlike = " and lower(k.kegiatan) like lower('%%%s%%')";
				$kodeuk = arg(3);
				$jenisrevisi = arg(4);
				$status = arg(5);
				$kegcari = arg(6);
				
				$qlike = sprintf(" and lower(keg.kegiatan) like ('%%%s%%')", strtolower($kegcari));
				//$kodeuk = '';
				if (!isSuperuser()) $kodeuk = apbd_getuseruk();
				
				break;

			case 'rekinv':
				$jenisrevisi = '0';
				$status = '100';
				$kegcari = '';

				$qlike = ' and keg.kodekeg in (SELECT apk.kodekeg FROM {anggperkegrevisi} apk INNER JOIN {rincianobyek} ro ON apk.kodero = ro.kodero AND apk.uraian <> ro.uraian)';
				
				$isinvalid = true;
				if (isSuperuser()) 
					$kodeuk = '00';
				else
					$kodeuk = apbd_getuseruk();
				
				break;				

			case 'filter':

				$nntitle ='';
				$kodeuk = arg(3);				
				$jenisrevisi = arg(4);
				$status = arg(5);
				$kegcari ='';

				break;

			case 'print':
				//sprintf(' and (k.jenisrevisi=\'%s\') ', $jenisrevisi);			
				//$qlike = " and lower(k.kegiatan) like lower('%%%s%%')";
			
				
				$kodeuk = arg(3);
				$jenisrevisi = arg(4);
				$status = arg(5);
				$kegcari = arg(6);
				  
				//if ($jenisrevisi == '') $jenisrevisi = 's';
				//if ($status == '') $status = 's';
				
				if (!isSuperuser()) $kodeuk = apbd_getuseruk();

				//$pdfFile = 'Daftar_Kegiatan_Revisi.pdf';
				$pdfFile = 'xxx.pdf';
				
				$htmlHeader = GenDataHeader($kodeuk);
				//$kodeuk, $jenisrevisi, $status, $kegcari
				$htmlContent = GenDataPrint($kodeuk , $jenisrevisi , $status , $kegcari);
				
				apbd_ExportPDF3(10, 10, $htmlHeader, $htmlContent,'', false, $pdfFile);
				
				//apbd_ExportPDF3_CF(10,10, $htmlHeader, $htmlHeader, '', false, $pdfFile, 1);
				//apbd_ExportPDF3_CF($topmargin,$topmargin, $htmlHeader, $htmlContent, $htmlFooter, false, $pdfFile, $hal1);

				//apbd_ExportPDF2($pageOrientation, $pageSize, $htmlContent1, $htmlContent2, $pdfFiel)
				apbd_ExportPDF2('L', 'F4', $htmlHeader, $htmlContent, $pdfFile);
				
				//return $htmlHeader;
				break;	
				
			case 'excel':
				kegiatanrevisi_exportexcel($tahun, $kodeuk);
				break;

			default:
				drupal_access_denied();
				break;
		}
	} else {
		$tahun = variable_get('apbdtahun', 0);

		//$qlike = ' and k.status=0 ';
		//$jenisrevisi = $_SESSION['jenisrevisi'];
		$jenisrevisi = '0';
		//$status = $_SESSION['statusrevisi'];
		$status = '100';
		//if ($status=='') $status=0;
		
		if (isSuperuser()) {
			$kodeuk = $_SESSION['kodeukrevisi'];	
			if ($kodeuk == '') 	$kodeuk = '00';
		} else
			$kodeuk = apbd_getuseruk();
	}

	
	if (isSuperuser()) {
		if (($kodeuk !='00') and ($kodeuk !='')) {
			$qlike .= sprintf(' and k.kodeuk=\'%s\' ', $kodeuk);
			$pquery = sprintf("select kodeuk, namasingkat from {unitkerja} where kodeuk='%s'", db_escape_string($kodeuk));
			$presult = db_query($pquery);
			if ($data=db_fetch_object($presult)) {
				$nntitle .= $data->namasingkat . ", ";
			}
		} 
							
	}  else {
		$qlike .= sprintf(' and k.kodeuk=\'%s\' ', $kodeuk);
	}	
	
	

	//jenisrevisi REVISI
	if ($jenisrevisi != '0') 
		$qlike .= sprintf(' and (k.jenisrevisi=\'%s\') ', $jenisrevisi);
	
	//Status
	if ($status!='100') $qlike .= sprintf(' and (k.status=\'%s\') ', $status);
	
	drupal_set_title($ntitle);
	
	//$output .= drupal_get_form('kegiatanrevisi_transfer_form');
	$output .= drupal_get_form('kegiatanrevisi_main_form');
	if (isSuperuser()) {
		$header = array (
			array('data' => 'No', 'width' => '10px', 'valign'=>'top'),
			array('data' => '', 'width' => '5px', 'valign'=>'top'),
			array('data' => 'SKPD', 'field'=> 'namasingkat', 'valign'=>'top'),
			array('data' => 'Kegiatan', 'field'=> 'kegiatan', 'valign'=>'top'),
			array('data' => 'Surat', 'valign'=>'top'),
			array('data' => 'Plafon','field'=> 'plafon', 'width' => '85px',  'valign'=>'top'),
			array('data' => 'Penetapan','field'=> 'penetapan', 'width' => '85px',  'valign'=>'top'),
			array('data' => 'Revisi','field'=> 'revisi', 'width' => '85px',  'valign'=>'top'),
			array('data' => 'Jenis', 'valign'=>'top', 'width' => '50px'),
			array('data' => 'Ubah Jenis', 'valign'=>'top', 'width' => '75px'),
			array('data' => 'Keterangan', 'valign'=>'top'),
			array('data' => ' ',  'valign'=>'top'),
		);
	} else {
		$header = array (
			array('data' => 'No', 'width' => '10px', 'valign'=>'top'),
			array('data' => '', 'width' => '5px', 'valign'=>'top'),
			array('data' => 'Kegiatan', 'field'=> 'kegiatan', 'valign'=>'top'),
			array('data' => 'Surat', 'valign'=>'top'),
			array('data' => 'Plafon','field'=> 'plafon', 'width' => '80px',  'valign'=>'top'),
			array('data' => 'Penetapan','field'=> 'penetapan', 'width' => '80px',  'valign'=>'top'),
			array('data' => 'Revisi','field'=> 'revisi', 'width' => '80px',  'valign'=>'top'),
			array('data' => 'Jenis', 'valign'=>'top', 'width' => '50px'),
			array('data' => 'Ubah Jenis', 'valign'=>'top', 'width' => '75px'),
			array('data' => 'Keterangan', 'valign'=>'top'),
			array('data' => ' ', 'valign'=>'top'),
		);
	}

	$tablesort = tablesort_sql($header);
    if ($tablesort=='') {
        $tablesort=' order by keg.kegiatan';
    } 
	
	//$customwhere = sprintf(' and k.tahun=%s ', $tahun);
	if (!isSuperuser()) {
		//$kodeuk = apbd_getuseruk();
		$customwhere .= sprintf(' and k.kodeuk=\'%s\' ', $kodeuk);	
	}	
    $where = ' where true' . $customwhere . $qlike ;
	
	//drupal_set_message($where);
	
	
	$sql = "select k.id, k.jenisrevisi, k.subjenisrevisi, k.kodekeg, k.triwulan,k.detiluraian,k.rab, k.tahun,k.kodeuk,keg.kegiatan,
			keg.plafon,keg.total revisi,kp.total penetapan, u.namasingkat,k.status, k.alasan1, k.jawaban, k.nosurat, k.tglsurat,
			keg.lokasi,keg.programsasaran, keg.programtarget, keg.jenis, keg.keluaransasaran, keg.keluarantarget, keg.hasilsasaran, keg.hasiltarget from {kegiatanrevisiperubahan} k inner join {kegiatanrevisi} keg on (k.kodekeg=keg.kodekeg) left join {kegiatanskpd} kp on (k.kodekeg=kp.kodekeg) inner join {unitkerja} u on ( k.kodeuk=u.kodeuk) " . $where;
			
	//$fsql = sprintf($sql, addslashes($nama));
	$fsql = $sql;
	//if (isSuperuser()) 
	//drupal_set_message($fsql);			
	
	//drupal_set_message($fsql);
    //echo $fsql;
    $countsql = "select count(*) as cnt from {kegiatanrevisiperubahan} k inner join {kegiatanrevisi} keg on (k.kodekeg=keg.kodekeg) " . $where;
    //$fcountsql = sprintf($countsql, addslashes($nama));
	$fcountsql = $countsql;
    $result = pager_query($fsql . $tablesort, $limit, 0, $fcountsql);
 
	//Jam,Menit,Detik,Bulan,Hari,Tahun
	
	$allowedit = (batastgl() || (isSuperuser()));
	
	//CEK TAHUN
	$allowedit = ($allowedit and ($tahun == variable_get('apbdtahun', 0)));
	
	//CEK DISPENSASI
	if ($allowedit==false) {
        $sqldisp = 'select dispensasirevisi from {unitkerja} where kodeuk=\'%s\'';
        $resdisp = db_query(db_rewrite_sql($sqldisp), array($kodeuk));
		if ($resdisp) {
			if ($datadisp = db_fetch_object($resdisp)) {
				$allowedit = $datadisp->dispensasirevisi;
			}
		}
	}
    
    $no=0;
    $page = $_GET['page'];
    if (isset($page)) {
        $no = $page * $limit;
    } else {
        $no = 0;
    }
    if ($result) {
        while ($data = db_fetch_object($result)) {
			
			$editlink ='';
			//if (($allowedit) or ($data->status==999)) {
			if ($allowedit) {
				$kegname = l($data->kegiatan, 'apbd/kegiatanrevisi/edit/' . $data->kodekeg . '/' . $data->id , array('html' =>TRUE));

				//KEGIATAN BARU
				/*
				if (isSuperuser())
					if (($data->jenisrevisi=='3') and ($data->subjenisrevisi=='2'))
						$editlink = l('Edit', 'apbd/kegiatanrevisi/edit4/' . $data->id . '/3/2/' . $data->kodekeg, array('html'=>TRUE)) . "&nbsp;";
					else
						$editlink = l('Edit', 'apbd/kegiatanrevisi/edit1/' . $data->id, array('html'=>TRUE)) . "&nbsp;";
				
				else
					$editlink = l('Edit', 'apbd/kegiatanrevisi/editkeg/' . $data->id .  '/3/2/' . $data->kodekeg, array('html'=>TRUE)) . "&nbsp;";
				*/
				
				if (isSuperuser())
					$editlink = l('Edit', 'apbd/kegiatanrevisi/edit1/' . $data->id, array('html'=>TRUE)) . "&nbsp;";
				else
					$editlink = l('Edit', 'apbd/kegiatanrevisi/editkeg/' . $data->id . '/' . $data->kodeuk . '/' . $data->kodekeg, array('html'=>TRUE)) . "&nbsp;";
				
				//KETERANGAN
				$str_ket = '';
				if ($data->alasan1=='') $str_ket = 'Alasan revisi belum diisi; ';	
				if ($data->nosurat=='') $str_ket .= 'Nomor surat permohonan revisi belum diisi; ';	
				if ($data->tglsurat=='') $str_ket .= 'Tanggal surat permohonan revisi belum diisi; ';	
				
				if ($str_ket=='') {
					if ($data->jenis=='2') {
						if (($data->programsasaran=='') or ($data->programtarget=='')) {
							$ket_tuk = 'Tolok ukur program belum diisi; ';
						} 
						if (($data->keluaransasaran=='') or ($data->keluarantarget=='')) {
							$ket_tuk .= 'Tolok ukur keluaran belum diisi; ';
						} 
						if (($data->hasilsasaran=='') or ($data->hasiltarget=='')) {
							$ket_tuk .= 'Tolok ukur hasil belum diisi; ';
						}
						if ($data->lokasi=='') {
							$ket_tuk .= 'Lokasi belum diisi; ';
						}
					}					
				}
				
				if ($data->jenisrevisi=='2') {
					if (($data->rab) or ($data->detiluraian)) {
						if ($str_ket =='') 
							$editlink .= l('Rek', 'apbd/kegiatanrevisi/rekening/' . $data->kodekeg  . '/' . $data->id , array('html'=>TRUE)) . "&nbsp;";
						else 
							$editlink .= '<font color="red">Rek</font>' . "&nbsp;";
							
					} else
						if (($isinvalid) and isSuperuser())
							$editlink .= l('Rek', 'apbd/kegiatanrevisi/rekening/' . $data->kodekeg  . '/' . $data->id , array('html'=>TRUE)) . "&nbsp;";
						else
							$editlink .= 'Rek' . "&nbsp;";
					
					if ($data->triwulan)
						$editlink .= l('TW', 'apbd/kegiatanrevisi/triwulan/' . $data->kodekeg, array('html'=>TRUE)) . "&nbsp;";
					else
						$editlink .= 'TW'  . "&nbsp;";
				 
				 
				} else {
					if ($str_ket =='') 
						$editlink .= l('Rek', 'apbd/kegiatanrevisi/rekening/' .  $data->kodekeg . '/' . $data->id , array('html'=>TRUE)) . "&nbsp;";
					else
						$editlink .= '<font color="red">Rek</font>' . "&nbsp;";
					
					if ($data->jenisrevisi>='3')
						$editlink .= l('TW', 'apbd/kegiatanrevisi/triwulan/' . $data->kodekeg , array('html'=>TRUE)) . "&nbsp;";
					else
						$editlink .= 'TW'  . "&nbsp;";
					
				}

				//HAPUS
				if (user_access('kegiatanskpd penghapusan')) {
					if (($data->status==0) or ($data->status==999))
						$editlink .=l('Hapus', 'apbd/kegiatanrevisi/delete/' . $data->id, array('html'=>TRUE))  . "&nbsp;";		 
					else 
						$editlink .= 'Hapus'  . "&nbsp;";
				}
				
			} else {
				
				$kegname = $data->kegiatan;
				
				$editlink = 'Edit' . "&nbsp;" . 'Rek' . "&nbsp;" . 'TW'  . "&nbsp;";
				
				$editlink .= 'Hapus'  . "&nbsp;";			 
				
			}
			
			//CETAK		
			$editlink .= l('Cetak', 'apbd/kegiatanskpd/printusulan/' . $data->id . '/10/rka/', array('html'=>TRUE)) ;
						
			if (isVerifikator()) {
				$editlink .= "&nbsp;" . l('Verifikasi', 'apbdverifikasi/' . $data->kodekeg, array('html'=>TRUE)) ;
				
			}			
            $no++;
			switch($data->jenisrevisi) {
				case '0':
					$jenis = '[0] New';
					break;			
				case '1':
					$jenis = '[1] Gsr';
					break;			
				case '2':
					$jenis = '[2] Adm';
					break;			
				case '3':
					$jenis = '[3] Trf';
					break;			
				case '4':
					$jenis = '[4] Drr';
					break;			
			}
			if ($data->status==0)
				$statusdesc = "<img src='/files/icon/edit.png'>";
			elseif ($data->status==1)
				$statusdesc = "<img src='/files/icon/cek.png'>";
			elseif ($data->status==9)
				$statusdesc = "<img src='/files/icon/stop.png'>";
			else
				$statusdesc = "<img src='/files/icon/info.png'>";
			
			$ubahjenis = apbdIsJenisBerubah($data->kodekeg,  $data->id);
			//$ubahjenis='';
			if ($str_ket!='') $str_ket = '<p><font color="Red" size="1px">' . $str_ket . '</font></p>';
			
			if (isSuperuser()) { 
				$rows[] = array (
					array('data' => $no, 'align' => 'right', 'valign'=>'top'),
					array('data' => $statusdesc, 'align' => 'right', 'valign'=>'top'),
					array('data' => $data->namasingkat, 'align' => 'left', 'valign'=>'top'),
					array('data' => $kegname . $str_ket, 'align' => 'left', 'valign'=>'top'),					
					array('data' => $data->nosurat, 'align' => 'left', 'valign'=>'top'),					
					array('data' => apbd_fn($data->plafon), 'align' => 'right', 'valign'=>'top'),
					array('data' => apbd_fn($data->penetapan), 'align' => 'right', 'valign'=>'top'),
					array('data' => apbd_fn($data->revisi), 'align' => 'right', 'valign'=>'top'),
					array('data' => $jenis, 'align' => 'left', 'valign'=>'top'),
					array('data' => $ubahjenis, 'align' => 'left', 'valign'=>'top'),
					array('data' => $data->jawaban, 'align' => 'left', 'valign'=>'top'),
					
					array('data' => $editlink, 'align' => 'left', 'valign'=>'top'),
				);
			} else { 
				$rows[] = array (
					array('data' => $no, 'align' => 'right', 'valign'=>'top'),
					array('data' => $statusdesc, 'align' => 'right', 'valign'=>'top'),
					array('data' => $kegname . $str_ket, 'align' => 'left', 'valign'=>'top'),					
					array('data' => $data->nosurat, 'align' => 'left', 'valign'=>'top'),					
					array('data' => apbd_fn($data->plafon), 'align' => 'right', 'valign'=>'top'),
					array('data' => apbd_fn($data->penetapan), 'align' => 'right', 'valign'=>'top'),
					array('data' => apbd_fn($data->revisi), 'align' => 'right', 'valign'=>'top'),
					array('data' => $jenis, 'align' => 'left', 'valign'=>'top'),
					array('data' => $ubahjenis, 'align' => 'left', 'valign'=>'top'),
					array('data' => $data->jawaban, 'align' => 'left', 'valign'=>'top'),
					
					array('data' => $editlink, 'align' => 'left', 'valign'=>'top'),
				);
			}
		
		}
    } 

	/*
	if ($no==0) {
		$linknew = l('Usulan Revisi', 'apbd/kegiatanrevisi/edit1/', array('html' =>TRUE));	
		$rows[] = array (
			array('data' => 'Tidak ada data revisi, klik ' . $linknew . ' untuk menambahkan.', 'colspan'=>'9')
		);
	}
	*/
		
	$btn = "";

	//$status = 0;
	$record = 0;
	
	if ($allowedit) {
		$btn .= l('Usulan Revisi', 'apbd/kegiatanrevisi/edit1/', array ('html' => true, 'attributes'=> array ('class'=>'btn_green', 'style'=>'color:white;'))) . "&nbsp;";
		$btn .= l('Kegiatan Baru', 'apbd/kegiatanrevisi/editnew/', array ('html' => true, 'attributes'=> array ('class'=>'btn_green', 'style'=>'color:white;'))) . "&nbsp;";
		
	}
	
	$btn .= l('Cari', 'apbd/kegiatanrevisi/find', array ('html' => true, 'attributes'=> array ('class'=>'btn_green', 'style'=>'color:white;'))) . "&nbsp;";
	 
	//	$uri = 'apbd/kegiatanrevisi/filter/' . $kodeuk . '/' . $jenisrevisi . '/' . $status;
	$btn .= l('Cetak' , 'apbd/kegiatanrevisi/print/' . $kodeuk . '/' . $jenisrevisi . '/' . $status . '/' . $kegcari, array ('html' => true, 'attributes'=> array ('class'=>'btn_green', 'style'=>'color:white;'))). "&nbsp;";
	
	if (isSuperuser()) {
		if ($kodeuk=='00')
			$btn .= l('Persetujuan', 'node/91', array ('html' => true, 'attributes'=> array ('class'=>'btn_green', 'style'=>'color:white;'))). "&nbsp;";
		else
			$btn .= l('Persetujuan', 'revisipersetujuan/' . $kodeuk . '/' . $jenisrevisi, array ('html' => true, 'attributes'=> array ('class'=>'btn_green', 'style'=>'color:white;'))). "&nbsp;";
	}
	//$btn .= l('DPPA-SKPD' , 'apbd/dpaperubahan', array ('html' => true, 'attributes'=> array ('class'=>'btn_green', 'style'=>'color:white;'))). "&nbsp;";
	
	
	//$btn .= "&nbsp;" . l('Simpan Excel', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn_green', 'style'=>'color:white;')));	

    $output .= $btn . theme_box('', theme_table($header, $rows)) . $btn;

	
	//    $output .= theme_box('', theme_table($header, $rows));
//	if (user_access('kegiatanskpd tambah'))
//		$output .= l("<img src='/files/button-add.png' title='Tambah data baru'>", 'apbd/kegiatanrevisi/edit/' , array('html'=>TRUE)) ;
//	if (user_access('kegiatanskpd pencarian'))		
//        $output .= l("<img src='/files/button-search.png' title='Pencarian data'>", 'apbd/kegiatanrevisi/find/' , array('html'=>TRUE)) ;
    $output .= theme ('pager', NULL, $limit, 0);
	 
	//if ($arg == 'print')
	//	return $htmlHeader . $htmlContent;
	//else		
		return $output;
	//return $htmlContent;
}


function kegiatanrevisi_main_form() {
	$form['formdata'] = array (
		'#type' => 'fieldset',
		'#title'=> 'Pilihan Data',
		'#collapsible' => TRUE,
		'#collapsed' => TRUE,        
	);
	$status = 0;
	$filter = arg(2);
	if (isset($filter) && ($filter=='filter')) {
		$kodeuk = arg(3);
		$jenisrevisi = arg(4);
		$status = arg(5);
		
	} else {
		//$jenisrevisi = $_SESSION['jenisrevisi'];
		$jenisrevisi = '0';
		//$status = $_SESSION['statusrevisi'];
		$status = '100';
		//if ($status=='') $status=0;
		
		if (isSuperuser()) {
			$kodeuk = $_SESSION['kodeukrevisi'];	
			if ($kodeuk == '') 	$kodeuk = '00';
		} else
			$kodeuk = apbd_getuseruk();
	}
	////drupal_set_message($filter);

	//if (isset($kodeuk)) {
	//    $form['formdata']['#collapsed'] = TRUE;
	//    //if (isUserKecamatan())
	//    //    if ($kodeuk != apbd_getuseruk())
	//    //        $form['formdata']['#collapsed'] = FALSE;
	//}
		   
	if (!isSuperuser() && !isVerifikator()) {
		$type = 'hidden';
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
		
	} else if(isSuperuser()){
		$pquery = "select kodedinas, kodeuk, namasingkat, namauk from {unitkerja} where aktif=1 order by kodedinas" ;
		$pres = db_query($pquery);
		$dinas = array();        
		
		$dinas['00'] ='00000 - SEMUA SKPD';
		while ($data = db_fetch_object($pres)) {
			$dinas[$data->kodeuk] = $data->kodedinas . ' - ' . $data->namasingkat;
		}
		
		$type='select';
	}
	else if (isVerifikator()) {

		if (isVerifikator()) {
			global $user;
			$username =  $user->name;		
			
			$where .= sprintf(' and us.username=\'%s\' ', $username);
		}
	
		$pquery = "select kodedinas, kodeuk, namasingkat, namauk from {unitkerja} where aktif=1 and kodeuk in (select k.kodeuk from {kegiatanrevisi} k inner join {userskpd} us on k.kodeuk=us.kodeuk " . $where . ") order by kodedinas" ;
		$pres = db_query($pquery);
		$dinas = array();        
		//drupal_set_message($pquery);
		$dinas['00'] ='00000 - SEMUA SKPD';
		while ($data = db_fetch_object($pres)) {
			$dinas[$data->kodeuk] = $data->kodedinas . ' - ' . $data->namasingkat;
		}
		
		$type='select';
		
		//drupal_set_message('Hai');
		
	}
	 
	$form['formdata']['kodeuk']= array(
		'#type'         => $type, 
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
		'#weight' => 3,		
	);	

	$form['formdata']['ss'] = array (
		'#type' => 'item',
		'#value' => "<div style='clear:both;'></div>",
		'#weight' => 4,
	);		

	$form['formdata']['status']= array(
		'#type' => 'radios', 
		'#title' => t('Status'), 
		'#default_value' => $status,
		'#options' => array(	
			 '100' => t('Semua'),
			 '0' => t('Usulan'), 	
			 '1' => t('Disetujui'), 	
			 '9' => t('Ditolak'),	
			 '999' => t('Perpanjang'),
		   ),
		'#weight' => 5,		
	);	

	$form['formdata']['ss1'] = array (
		'#type' => 'item',
		'#value' => "<div style='clear:both;'></div>",
		'#weight' => 6,
	);		

	$form['formdata']['submit'] = array (
		'#type' => 'submit',
		'#value' => 'Tampilkan',
		'#weight' => 7
	);
	
	return $form;
}

function kegiatanrevisi_main_form_submit($form, &$form_state) {
	$kodeuk = $form_state['values']['kodeuk'];
	$jenisrevisi = $form_state['values']['jenisrevisi'];
	$status = $form_state['values']['status'];
	
	//$tahun= $form_state['values']['tahun'];
	
	$_SESSION['jenisrevisi'] = $jenisrevisi;
	$_SESSION['statusrevisi'] = $status;
	
	if (isSuperuser()) 
		$_SESSION['kodeukrevisi'] = $kodeuk;
	 
	$uri = 'apbd/kegiatanrevisi/filter/' . $kodeuk . '/' . $jenisrevisi . '/' . $status;
	//$uri = 'apbd/kegiatanrevisi/filter/' . $kodeuk . '/' . $status . '/' . $jenisrevisi;
	drupal_goto($uri);
	
}

function GenDataHeader($kodeuk) {

    $tahun = variable_get('apbdtahun', 0);
	$revisi = variable_get('apbdrevisi', 0);
	
	$rowsjudul[] = array (array ('data'=>'REVISI #'  . $revisi . ' - APBD KABUPATEN JEPARA - TAHUN ' . $tahun, 'width'=>'875px', 'colspan'=>'3', 'style' =>'border:none; font-weight:900; font-size:1.3em; text-align:center;'));
	if (($kodeuk !='00') and ($kodeuk !='')) {
		$sql = 'select namauk from {unitkerja} where kodeuk=\'%s\'' ;
		$res = db_query(db_rewrite_sql($sql), array ($kodeuk));
		if ($res) {
			if ($data = db_fetch_object($res)) {
				$skpd = $data->namauk;
			}
		}
				
	} else
		$skpd = 'KABUPATEN JEPARA';;
	$rowsjudul[] = array (array ('data'=>$skpd, 'width'=>'875px', 'colspan'=>'3', 'style' =>'border:none; font-weight:900; font-size:1.3em; text-align:center;'));
	$rowsjudul[] = array (array ('data'=>'', 'width'=>'875px', 'colspan'=>'3', 'style' =>'border:none; font-weight:900; font-size:1.3em; text-align:center;'));
	

	$opttbl = array('cellspacing'=>'1', 'cellpadding'=>'1', 'border' => '0');
	$headerkosong = array();

	//$output = theme_box('', apbd_theme_table($headerkosong, $rowsjudul, $opttbl));
	$output = theme_box('', apbd_theme_table($headerkosong, $rowsjudul, $opttbl));
	
	return $output;
	
}

function GenDataPrint($kodeuk, $jenisrevisi, $status, $kegcari) {
	
	
	//set_time_limit(0);
	//ini_set('memory_limit', '640M');
	
	$totalPlafon =0;
	$totalPenetapan =0;
	$totalRevisi =0;
	
	
	$headersrek[] = array (
						 
						 array('data' => 'NO.',  'width'=> '25px','rowspan'=>'2', 'style' => 'border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'KEGIATAN',  'width' => '260px', 'rowspan'=>'2','style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'SKPD',  'width' => '70px', 'rowspan'=>'2','style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'URAIAN REVISI',  'width' => '250px', 'colspan'=>'3', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
						 array('data' => 'JUMLAH ANGGARAN',  'width' => '270px', 'colspan'=>'3', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;'),
					);


	
	$headersrek[] = array (
						 
						 array('data' => 'JENIS',  'width' => '40px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;'),
						 array('data' => 'ALASAN',  'width' => '120px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;'),
						 array('data' => 'SURAT',  'width' => '90px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;'),
						 array('data' => 'PLAFON',  'width' => '90px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;'),
						 array('data' => 'PENETAPAN',  'width' => '90px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;'),
						 array('data' => 'REVISI',  'width' => '90px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;'),
					);

	
	
	
	
	//if ($kegcari !='') $qlike = sprintf(" and lower(keg.kegiatan) like lower('%%%s%%')", $kegcari);
	if (strlen($kegcari) >0 ) $qlike = sprintf(" and lower(keg.kegiatan) like lower('%%%s%%')", $kegcari);

	if (($kodeuk !='00') and ($kodeuk !='')) 
		$qlike .= sprintf(' and k.kodeuk=\'%s\' ', $kodeuk);

	//jenisrevisi REVISI
	if ($jenisrevisi !='0')  
		$qlike .= sprintf(' and (k.jenisrevisi=%s) ', $jenisrevisi);
	 
	//Status
	if ($status != '100')  $qlike .= sprintf(' and (k.status=%s) ', $status);
	
	$tablesort=' order by u.namasingkat, keg.kegiatan';
	
	$tahun = variable_get('apbdtahun', 0);
	$customwhere = sprintf(' and k.tahun=%s ', $tahun);
    $where = ' where true' . $customwhere . $qlike ;
	
	$sql = "select k.id, k.jenisrevisi, k.subjenisrevisi, k.kodekeg, k.triwulan,k.detiluraian,k.rab, k.tahun,k.kodeuk,keg.kegiatan,
			keg.plafon,keg.total revisi,kp.total penetapan, u.namasingkat,k.status, keg.sumberdana1, k.alasan1, k.nosurat 
			from {kegiatanrevisiperubahan} k inner join {kegiatanrevisi} keg on (k.kodekeg=keg.kodekeg) 
			left join {kegiatanskpd} kp on (k.kodekeg=kp.kodekeg) inner join {unitkerja} u on ( k.kodeuk=u.kodeuk) " . $where . $tablesort;
	
	//if (isSuperuser()) drupal_set_message($sql);
	$result = db_query($sql);

	
	if ($result) {
		while ($data = db_fetch_object($result)) {
			$no += 1;

			switch($data->jenisrevisi) {
				case '1':
					$jenis = '[1] Gsr';
					break;			
				case '2':
					$jenis = '[2] Adm';
					break;			
				case '3':
					$jenis = '[3] Trf';
					break;			
				case '4':
					$jenis = '[4] Drr';
					break;			
			}			
			$totalPlafon += $data->plafon;
			$totalPenetapan += $data->penetapan;
			$totalRevisi += $data->revisi;
			
			//if ($data->sumberdana1=='LAIN-LAIN PENDAPATAN')
			//	$sd = 'LLP';
			//else
			//	$sd = $data->sumberdana1;
			$rowsrek[] = array (
								 array('data' => $no,  'width'=> '25px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:right;'),
								 array('data' => $data->kegiatan ,  'width' => '260px', 'style' => ' border-right: 1px solid black; text-align:left;'),
								 array('data' => $data->namasingkat,  'width' => '70px', 'style' => ' border-right: 1px solid black; text-align:left;'),
								 array('data' => $jenis,  'width' => '40px', 'style' => ' border-right: 1px solid black; text-align:left;'),
								 array('data' => $data->alasan1,  'width' => '120px', 'style' => ' border-right: 1px solid black; text-align:left;'),								 
								 array('data' => $data->nosurat,  'width' => '90px', 'style' => ' border-right: 1px solid black; text-align:left;'),								 								 
								 array('data' => apbd_fn($data->plafon),  'width' => '90px', 'style' => ' border-right: 1px solid black; text-align:right;'),
								 array('data' => apbd_fn($data->penetapan),  'width' => '90px', 'style' => ' border-right: 1px solid black; text-align:right;'),
								 array('data' => apbd_fn($data->revisi),  'width' => '90px', 'style' => ' border-right: 1px solid black; text-align:right;'),
								 );				
 
		}
	}										 
	
	
	
	$rowsrek[] = array (
						 array('data' => '',  'width'=> '25px', 'style' => 'border-left: 1px solid black;  border-top: 2px solid black; border-bottom: 1px solid black; text-align:right;'),
						 array('data' => 'TOTAL',  'width' => '580px', 'colspan' => '5', 'style' => ' border-top: 2px solid black; border-bottom: 1px solid black; border-right: 1px solid black; text-align:left;'),
						 array('data' => apbd_fn($totalPlafon),  'width' => '90px', 'style' => ' border-right: 1px solid black; border-top: 2px solid black; border-bottom: 1px solid black; text-align:right;'),
						 array('data' => apbd_fn($totalPenetapan),  'width' => '90px', 'style' => ' border-right: 1px solid black; border-top: 2px solid black; border-bottom: 1px solid black; text-align:right;'),
						 array('data' => apbd_fn($totalRevisi),  'width' => '90px', 'style' => ' border-right: 1px solid black; border-top: 2px solid black; border-bottom: 1px solid black; text-align:right;'),
						 );				

	
	
	$opttbl = array('cellspacing'=>'1', 'cellpadding'=>'1', 'border' => '1');
	$output = theme_box('', apbd_theme_table($headersrek, $rowsrek, $opttbl));
	
	//$output = theme_box('', apbd_theme_table($headerkosong, $rowsjudul, $opttbl));
	
	
	//$output = 'Hallo';
	return $output;
	
}


function kegiatanrevisi_transfer_form() {

}


?>