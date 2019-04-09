<?php
function kegiatanskpd_printdpap_test_main() {
	//$topmargin = '20';
	$kodekeg = arg(3);
			
	$output = getlaporan($kodekeg, $revisi);
	apbd_ExportPDF('L', 'F4', $output, 'Register SPM.pdf');
}

function getlaporan($kodekeg, $revisi) {

	$bintangkegiatan = false;
	$sql = 'select bintang from {kegiatanperubahan} where kodekeg=\'%s\'';
	$fsql = sprintf($sql, db_escape_string($kodekeg));	
	$res = db_query($fsql);
	if ($res) {
		if ($data = db_fetch_object($res)) {
			$bintangkegiatan = ($data->bintang=='1');
		}
	}
	
	
	set_time_limit(0);
	ini_set('memory_limit', '640M');
	
	$total = 0;
	$totalpen = 0;


	$headersrek[] = array (
						 array('data' => 'KODE',  'width'=> '60px', 'rowspan'=>'2','style' => 'border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;font-size:small;'),
						 
						 array('data' => 'URAIAN',  'width' => '230x','rowspan'=>'2','colspan'=>'2', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;font-size:small;'),
						 array('data' => 'SEBELUM PERUBAHAN', 'width' => '240px','colspan'=>'4','style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;font-size:small;'),
						 array('data' => 'SETELAH PERUBAHAN', 'width' => '240px','colspan'=>'4','style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;font-size:small;'),
						 array('data' => 'BERTAMBAH /BERKURANG',  'width' => '105px','colspan'=>'2', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 2px solid black; text-align:center;font-size:small;'),
						 );
	$headersrek[] = array (

						 array('data' => 'Satuan', 'width' => '60px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;font-size:small;'),
						 array('data' => 'Volume', 'width' => '60px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;font-size:small;'),
						 array('data' => '@Harga',  'width' => '60px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;font-size:small;'),
						 array('data' => 'Jumlah',  'width' => '60px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;font-size:small;'),

						 array('data' => 'Satuan', 'width' => '60px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;font-size:small;'),
						 array('data' => 'Volume', 'width' => '60px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;font-size:small;'),
						 array('data' => '@Harga',  'width' => '60px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;font-size:small;'),
						 array('data' => 'Jumlah',  'width' => '60px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;font-size:small;'),

						 array('data' => 'Rupiah', 'width' => '70px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;font-size:small;'),
						 array('data' => '%', 'width' => '35px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; text-align:center;font-size:small;'),
						 );

			//OBYEK
			$sql = 'select mid(k.kodero,1,5) kodeo,o.uraian,sum(jumlah) jumlahx,sum(jumlahp) jumlahxp from {anggperkegperubahan} k  left join {obyek} o on mid(k.kodero,1,5)=o.kodeo 
				   where kodekeg=\'%s\'';
			$fsql = sprintf($sql, db_escape_string($kodekeg));
			$fsql .= ' group by mid(k.kodero,1,5),o.uraian order by mid(k.kodero,1,5)';
			
			////////drupal_set_message( $fsql);
			$resultobyek = db_query($fsql);
			if ($resultobyek) {
				while ($dataobyek = db_fetch_object($resultobyek)) {	
								 
					//REKENING
					$sql = 'select kodero,uraian,jumlah,jumlahp,anggaran,bintang from {anggperkegperubahan} k where kodekeg=\'%s\' and mid(k.kodero,1,5)=\'%s\'';
					$fsql = sprintf($sql, db_escape_string($kodekeg), db_escape_string($dataobyek->kodeo));
					
					////////drupal_set_message( $fsql);
					$fsql .= ' order by k.kodero';
					$result = db_query($fsql);
					if ($result) {
						while ($data = db_fetch_object($result)) {
						
						//BINTANG
						$alldetilbintang = false;
						if ($bintangkegiatan) {
							$statusrek = '<font color="Red">*</font>';
							$alldetilbintang = true;
								
						} else {
							//if (($data->anggaran==0) and ($data->jumlahp>0)) {
							if ($data->bintang=='1') {
								
								$statusrek = '<font color="Red">*</font>';
								$alldetilbintang = true;

							} else {
								
								$statusrek = '';

							}
						}							
 
												
						$penrekening = $data->jumlah;
						$persen = apbd_hitungpersen($penrekening, $data->jumlahp);
						$rowsrek[] = array (
											 array('data' => apbd_format_rek_rincianobyek($data->kodero),  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;font-size:small;'),
											 array('data' => $data->uraian . $statusrek,  'width' => '230x', 'colspan'=>'2', 'style' => ' border-right: 1px solid black; text-align:left;font-size:small;'),
											 array('data' => '', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;'),
											 array('data' => '', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;'),
											 array('data' => '',  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;'),
											 array('data' => apbd_fn($penrekening),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:bold;'),

											 array('data' => '', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;'),
											 array('data' => '', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;'),
											 array('data' => '',  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;'),
											 array('data' => apbd_fn($data->jumlahp),  'width' => '60px', 'style' => ' border-right: 1px solid black;  text-align:right;font-size:small;font-weight:bold;'),

											 array('data' => apbd_fn($data->jumlahp - $penrekening),  'width' => '70px', 'style' => ' border-right: 1px solid black;  text-align:right;font-size:small;font-weight:bold;'),
											 array('data' => apbd_fn1($persen),  'width' => '35px', 'style' => ' border-right: 1px solid black;  text-align:right;font-size:small;font-weight:bold;'),
											 
										);
							
							$totalpen += $penrekening;
							$total += $data->jumlahp;
										
							//DETIL
							$sql = 'select distinct iddetil,uraian,unitjumlah,unitsatuan,volumjumlah,volumsatuan,harga,total, pengelompokan, anggaran,bintang from {anggperkegdetilperubahan} where total>=0 and kodekeg=\'%s\' and kodero=\'%s\' order by nourut asc,iddetil';
							$fsql = sprintf($sql, db_escape_string($kodekeg), db_escape_string($data->kodero));
							//drupal_set_message($fsql);

							$resultdetil = db_query($fsql);
							
							if ($resultdetil) {
								while ($datadetil = db_fetch_object($resultdetil)) {
									$statusdetil = '';
									if ($penrekening > 0) {
										$sql = 'select iddetil,uraian,unitjumlah,unitsatuan,volumjumlah,volumsatuan,harga,total, pengelompokan,anggaran,bintang from {anggperkegdetil} where total>=0 and kodekeg=\'%s\' and kodero=\'%s\' and iddetil=\'%s\'';
										$fsql = sprintf($sql, $kodekeg, $data->kodero, db_escape_string($datadetil->iddetil));
										
										////////drupal_set_message($fsql);
										$datadetilpenuraian = '';
										$datadetilpentotal = 0;
										$datadetilpenanggaran = 0;
										$datadetilbintang = 0;
										$resultdetilpen = db_query($fsql);
										if ($datadetilpen = db_fetch_object($resultdetilpen)) {
											$datadetilpenuraian = $datadetilpen->uraian;
											$datadetilpentotal = $datadetilpen->total;
											$datadetilpenanggaran = $datadetilpen->anggaran;
											$datadetilbintang = $datadetilpen->bintang;
										}
										
										//bintang penetapan
										if ($datadetilbintang==1) {
											$statusdetil_pen = '<font color="Red">*</font>';

										} else {
											$statusdetil_pen = '';
											
										}
										
										
									} else {
										$unitjumlahpen = '';
										$volumjumlahpen = '';
										$hargasatuanpen = '';

										$datadetilpenuraian = '';
										$datadetilpentotal = 0;
										
									}	
									
									//PERBINTANGAN
									if ($alldetilbintang) {
										$statusdetil = '<font color="Red">*</font>';
										$allsubbintang = true;
										
									} else {
										//if (($datadetil->anggaran==0) and ($datadetil->total>0)) {
										if ($datadetil->bintang==1)  {
											$statusdetil = '<font color="Red">*</font>';
											$allsubbintang = true;
											//drupal_set_message('btg');
										} else {
											$statusdetil = '';
											$allsubbintang = false;
											
										}
										
									}										
									
									if ($datadetil->pengelompokan) {
										$unitjumlah = '';
										$volumjumlah = '';
										$hargasatuan = '';
										$bullet = '#';

										$unitjumlahpen = '';
										$volumjumlahpen = '';
										$hargasatuanpen = '';
										
									} else {
										$unitjumlah = $datadetil->unitjumlah . ' ' . $datadetil->unitsatuan;
										$volumjumlah = $datadetil->volumjumlah . ' ' . $datadetil->volumsatuan;
										$hargasatuan = apbd_fn($datadetil->harga);
										$bullet = '-';
										
										if ($penrekening > 0) {
										$unitjumlahpen = $datadetilpen->unitjumlah . ' ' . $datadetilpen->unitsatuan;
										$volumjumlahpen = $datadetilpen->volumjumlah . ' ' . $datadetilpen->volumsatuan;
										$hargasatuanpen = apbd_fn($datadetilpen->harga);
										//$bullet = '•';
										}
										
									}
									 
									
									$persen = apbd_hitungpersen($datadetilpentotal, $datadetil->total);
									if ($datadetil->uraian == $datadetilpenuraian) {
										$rowsrek[] = array (
															 array('data' => '',  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;font-size:small;'),
			 												 array('data' => $bullet,  'width' => '15px', 'style' => 'text-align:right;font-size:small;'),
															 array('data' => $datadetil->uraian . $statusdetil,  'width' => '215px', 'style' => ' border-right: 1px solid black; text-align:left;font-size:small;font-weight:lighter;'),
															 array('data' => $unitjumlahpen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;'),
															 array('data' => $volumjumlahpen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;'),
															 array('data' => $hargasatuanpen,  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
															 array('data' => apbd_fn($datadetilpentotal),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),

															 array('data' => $unitjumlah, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;'),
															 array('data' => $volumjumlah, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;'),
															 array('data' => $hargasatuan,  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
															 array('data' => apbd_fn($datadetil->total),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
															 
															 array('data' => apbd_fn($datadetil->total - $datadetilpentotal),  'width' => '70px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
															 array('data' => apbd_fn1($persen),  'width' => '35px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
															 );
															 
										if ($datadetil->pengelompokan) {
											//SUB DETIL
											$sql = 'select idsub,uraian,unitjumlah,unitsatuan,volumjumlah,volumsatuan, harga,total,anggaran,bintang from {anggperkegdetilsubperubahan} where total>=0 and iddetil=\'%s\' order by nourut asc,idsub';
											$fsql = sprintf($sql, db_escape_string($datadetil->iddetil));
											//////////drupal_set_message($fsql);
											
											//$no = 0;
											$resultsub = db_query($fsql);
											if ($resultsub) {
												while ($datasub = db_fetch_object($resultsub)) {
													//$no += 1;

													$datasuburaian_pen = '';
													$datasubunitjumlah_pen = '';
													$datasubvolumjumlah_pen = '';
													$datasubharga_pen = '';
													$datasubtotal_pen = 0; 

													$uraian_rev = str_replace("*","",$datasub->uraian);

													if (($penrekening > 0) and ($datadetil->uraian == $datadetilpenuraian)) {
														
												
														$statusdetilsub_pen = '';
														$sql = 'select idsub,uraian,unitjumlah,unitsatuan,volumjumlah,volumsatuan, harga,total,anggaran,bintang from {anggperkegdetilsub} where total>=0 and iddetil=\'%s\' and idsub=\'%s\'';
														$fsql = sprintf($sql, db_escape_string($datadetil->iddetil), db_escape_string($datasub->idsub));
														$resultsubpen = db_query($fsql);
														if ($resultsubpen) {
															if ($datasubpen = db_fetch_object($resultsubpen)) {
																$datasuburaian_pen = $datasubpen->uraian;
																$datasubunitjumlah_pen = $datasubpen->unitjumlah . ' ' . $datasubpen->unitsatuan;
																$datasubvolumjumlah_pen = $datasubpen->volumjumlah . ' ' . $datasubpen->volumsatuan;
																$datasubharga_pen = $datasubpen->harga;
																$datasubtotal_pen = $datasubpen->total;

																//if ($datasubpen->anggaran==0) 
																if ($datasubpen->bintang==1) 
																	$statusdetilsub_pen = '<font color="Red">*</font>';
																else
																	$statusdetilsub_pen = '';
																
															
															} 
															  
														}  
														     
														  
														//recek
														if (($datasuburaian_pen=='') and ($datasubtotal_pen == $datasub->total)) $datasuburaian_pen = $uraian_rev;
														
													} 
													
													//PERBINTANGAN
													//if (($datasub->anggaran==0) and ($datasub->total>0)) 
													if ($datasub->bintang==1)  
														$statusdetilsub = '<font color="Red">*</font>';
													else
														$statusdetilsub = '';
													
													if ($statusdetil != '') $statusdetilsub = $statusdetil;
														
													if ($datasuburaian_pen == $uraian_rev) {
														$persen = apbd_hitungpersen($datasubtotal_pen, $datasub->total);
													
														$rowsrek[] = array (
																 array('data' => '',  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;font-size:small;'),
																 array('data' => '',  'width' => '15px', 'style' => 'text-align:right;font-size:small;'),
																 array('data' =>  '- ' . $datasub->uraian . $statusdetilsub,  'width' => '215px', 'style' => ' border-right: 1px solid black; text-align:left;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => $datasubunitjumlah_pen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => $datasubvolumjumlah_pen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn($datasubharga_pen),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn($datasubtotal_pen),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																 array('data' => $datasub->unitjumlah . ' ' . $datasub->unitsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => $datasub->volumjumlah . ' ' . $datasub->volumsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn($datasub->harga),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn($datasub->total),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																 array('data' => apbd_fn($datasub->total - $datasubtotal_pen),  'width' => '70px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn1($persen),  'width' => '35px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																 );	
													
													} else if (trim($datasuburaian_pen) == trim($datasub->uraian)) {
														$persen = apbd_hitungpersen($datasubtotal_pen, $datasub->total);
													
														$rowsrek[] = array (
																 array('data' => '',  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;font-size:small;'),
																 array('data' => '',  'width' => '15px', 'style' => 'text-align:right;font-size:small;'),
																 array('data' =>  '- ' . $datasub->uraian . $statusdetilsub,  'width' => '215px', 'style' => ' border-right: 1px solid black; text-align:left;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => $datasubunitjumlah_pen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => $datasubvolumjumlah_pen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn($datasubharga_pen),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn($datasubtotal_pen),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																 array('data' => $datasub->unitjumlah . ' ' . $datasub->unitsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => $datasub->volumjumlah . ' ' . $datasub->volumsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn($datasub->harga),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn($datasub->total),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																 array('data' => apbd_fn($datasub->total - $datasubtotal_pen),  'width' => '70px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn1($persen),  'width' => '35px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																 );															
														
													} else {
														
														if ($datasuburaian_pen == $datasub->uraian) {
															$persen = apbd_hitungpersen($datasubtotal_pen, $datasub->total);
														
															$rowsrek[] = array (
																	 array('data' => '',  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;font-size:small;'),
																	 array('data' => '',  'width' => '15px', 'style' => 'text-align:right;font-size:small;'),
																	 array('data' =>  '- ' . $datasub->uraian . $statusdetilsub,  'width' => '215px', 'style' => ' border-right: 1px solid black; text-align:left;font-size:small;font-weight:lighter;font-style: italic;'),
																	 array('data' => $datasubunitjumlah_pen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																	 array('data' => $datasubvolumjumlah_pen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																	 array('data' => apbd_fn($datasubharga_pen),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																	 array('data' => apbd_fn($datasubtotal_pen),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																	 array('data' => $datasub->unitjumlah . ' ' . $datasub->unitsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																	 array('data' => $datasub->volumjumlah . ' ' . $datasub->volumsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																	 array('data' => apbd_fn($datasub->harga),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																	 array('data' => apbd_fn($datasub->total),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																	 array('data' => apbd_fn($datasub->total - $datasubtotal_pen),  'width' => '70px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																	 array('data' => apbd_fn1($persen),  'width' => '35px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																	 );																
															
														} else {
															
															if (trim($datasuburaian_pen) == trim($datasub->uraian)) {
																$persen = apbd_hitungpersen($datasubtotal_pen, $datasub->total);
																
																	$rowsrek[] = array (
																			 array('data' => '',  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;font-size:small;'),
																			 array('data' => '',  'width' => '15px', 'style' => 'text-align:right;font-size:small;'),
																			 array('data' =>  '- ' . $datasub->uraian . $statusdetilsub,  'width' => '215px', 'style' => ' border-right: 1px solid black; text-align:left;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => $datasubunitjumlah_pen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => $datasubvolumjumlah_pen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
						 													 array('data' => apbd_fn($datasubharga_pen),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => apbd_fn($datasubtotal_pen),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																			 array('data' => $datasub->unitjumlah . ' ' . $datasub->unitsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => $datasub->volumjumlah . ' ' . $datasub->volumsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => apbd_fn($datasub->harga),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => apbd_fn($datasub->total),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																			 array('data' => apbd_fn($datasub->total - $datasubtotal_pen),  'width' => '70px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => apbd_fn1($persen),  'width' => '35px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																			 );																		
															} else { 
																
																if ($datasub->idsub == '15610') {

																	$persen = apbd_hitungpersen($datasubtotal_pen, $datasub->total);
																	
																		$rowsrek[] = array (
																				 array('data' => '',  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;font-size:small;'),
																				 array('data' => '',  'width' => '15px', 'style' => 'text-align:right;font-size:small;'),
																				 array('data' =>  '- ' . $datasub->uraian . $statusdetilsub,  'width' => '215px', 'style' => ' border-right: 1px solid black; text-align:left;font-size:small;font-weight:lighter;font-style: italic;'),
																				 array('data' => $datasubunitjumlah_pen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																				 array('data' => $datasubvolumjumlah_pen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																				 array('data' => apbd_fn($datasubharga_pen),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																				 array('data' => apbd_fn($datasubtotal_pen),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																				 array('data' => $datasub->unitjumlah . ' ' . $datasub->unitsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																				 array('data' => $datasub->volumjumlah . ' ' . $datasub->volumsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																				 array('data' => apbd_fn($datasub->harga),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																				 array('data' => apbd_fn($datasub->total),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																				 array('data' => apbd_fn($datasub->total - $datasubtotal_pen),  'width' => '70px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																				 array('data' => apbd_fn1($persen),  'width' => '35px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																				 );		
																
																} else {
																if (($datasuburaian_pen !='') and ($datasubtotal_pen>0)) {

																	$rowsrek[] = array (
																			 array('data' => '',  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;font-size:small;'),
																			 array('data' => '',  'width' => '15px', 'style' => 'text-align:right;font-size:small;'),
																			 array('data' =>  '- ' . $datasuburaian_pen . $statusdetilsub_pen,  'width' => '215px', 'style' => ' border-right: 1px solid black; text-align:left;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => $datasubunitjumlah_pen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => $datasubvolumjumlah_pen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => apbd_fn($datasubharga_pen),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => apbd_fn($datasubtotal_pen),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																			 array('data' => '0', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => '0', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => '0',  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => '0',  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																			 array('data' => apbd_fn(-$datasubtotal_pen),  'width' => '70px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => apbd_fn1(-100),  'width' => '35px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																			 );	
																}  
																

																if ($datasub->bintang==1)  $statusdetilsub = '<font color="Red">*</font>';
																
																if ($datasub->uraian!='') {
																	$rowsrek[] = array (
																			 array('data' => '',  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;font-size:small;'),
																			 array('data' => '',  'width' => '15px', 'style' => 'text-align:right;font-size:small;'),
																			 array('data' =>  '- ' . $datasub->uraian . $statusdetilsub,  'width' => '215px', 'style' => ' border-right: 1px solid black; text-align:left;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => '0', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => '0', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => '0',  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => '0',  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																			 array('data' => $datasub->unitjumlah . ' ' . $datasub->unitsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => $datasub->volumjumlah . ' ' . $datasub->volumsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => apbd_fn($datasub->harga),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => apbd_fn($datasub->total),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																			 array('data' => apbd_fn($datasub->total),  'width' => '70px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																			 array('data' => apbd_fn1(100),  'width' => '35px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																			);	
																	}		
																}
															}
														}		 
													}
													
													//$$$
												}
											}
											
											//###
										}
										
									
									} else {
										
										//***
										if (($datadetilpenuraian) !='' and ($datadetilpentotal>0)) {
											$rowsrek[] = array (
																 array('data' => '',  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;font-size:small;'),
																 array('data' => $bullet,  'width' => '15px', 'style' => 'text-align:right;font-size:small;'),
																 array('data' => $datadetilpenuraian . $statusdetil_pen,  'width' => '215px', 'style' => ' border-right: 1px solid black; text-align:left;font-size:small;font-weight:lighter;'),
																 array('data' => $unitjumlahpen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;'),
																 array('data' => $volumjumlahpen, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;'),
																 array('data' => $hargasatuanpen,  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
																 array('data' => apbd_fn($datadetilpentotal),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),

																 array('data' => '0', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;'),
																 array('data' => '0', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;'),
																 array('data' => '0',  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
																 array('data' => apbd_fn1(0),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
																 
																 array('data' => apbd_fn(-$datadetilpentotal),  'width' => '70px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
																 array('data' => apbd_fn1(-100),  'width' => '35px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
																 );
											//####
											if ($datadetil->pengelompokan) {

											
												//SUB DETIL
												$sql = 'select idsub,uraian,unitjumlah,unitsatuan,volumjumlah,volumsatuan, harga,total,anggaran from {anggperkegdetilsub} where total>=0 and iddetil=\'%s\' order by nourut asc,idsub';
												$fsql = sprintf($sql, db_escape_string($datadetil->iddetil));
												
													
												$resultsub = db_query($fsql);
												if ($resultsub) {
													while ($datasub = db_fetch_object($resultsub)) {
														//$no += 1;
														
														$statusdetilsub_pen = '';
														//if ($datasub->total==$datasub->anggaran)
														//	$statusdetilsub_pen = '';
														//else
														//	$statusdetilsub_pen = '<font color="Red">*</font>';
														
														$rowsrek[] = array (
																 array('data' => '',  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;font-size:small;'),
																 array('data' => '',  'width' => '15px', 'style' => 'text-align:right;font-size:small;'),
																 array('data' => '- ' . $datasub->uraian . $statusdetilsub_pen,  'width' => '215px', 'style' => ' border-right: 1px solid black; text-align:left;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => $datasub->unitjumlah . ' ' . $datasub->unitsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => $datasub->volumjumlah . ' ' . $datasub->volumsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn($datasub->harga),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn($datasub->total),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																 array('data' => '0', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => '0', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => '0',  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => '0',  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																 array('data' => apbd_fn(-$datasub->total),  'width' => '70px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn1(-100),  'width' => '35px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																);	
													}
												}
											}
											//###
											
										} 
										
										if (($datadetil->uraian) !='' and ($datadetil->total<>0)) {
											//if (($datadetil->total>0) and ($datadetil->anggaran==0))
											if ($datadetil->bintang==1) 
												$statusdetilsub = '<font color="Red">*</font>';
											else
												$statusdetilsub = '';
											
											$rowsrek[] = array (
																 array('data' => '',  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;font-size:small;'),
																 array('data' => $bullet,  'width' => '15px', 'style' => 'text-align:right;font-size:small;'),
																 array('data' => $datadetil->uraian . $statusdetilsub,  'width' => '215px', 'style' => ' border-right: 1px solid black; text-align:left;font-size:small;font-weight:lighter;'),
																 array('data' => '0', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;'),
																 array('data' => '0', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;'),
																 array('data' => '0',  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
																 array('data' => apbd_fn1(0),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),

																 array('data' => $unitjumlah, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;'),
																 array('data' => $volumjumlah, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;'),
																 array('data' => $hargasatuan,  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
																 array('data' => apbd_fn($datadetil->total),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
																 
																 array('data' => apbd_fn($datadetil->total),  'width' => '70px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
																 array('data' => apbd_fn1(100),  'width' => '35px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;'),
																 );
											//####
											if ($datadetil->pengelompokan) {

											
												//SUB DETIL
												$sql = 'select idsub,uraian,unitjumlah,unitsatuan,volumjumlah,volumsatuan, harga,total,anggaran,bintang from {anggperkegdetilsubperubahan} where total>=0 and iddetil=\'%s\' order by nourut asc,idsub';
												$fsql = sprintf($sql, db_escape_string($datadetil->iddetil));
												
													
												$resultsub = db_query($fsql);
												if ($resultsub) {
													while ($datasub = db_fetch_object($resultsub)) {
														//$no += 1;
														
														//if (($datasub->total>0) and ($datasub->anggaran==0))
														if ($datasub->bintang==1) 
															$statusdetilsub_pen = '<font color="Red">*</font>';
														else
															$statusdetilsub_pen = '';
														
														$rowsrek[] = array (
																 array('data' => '',  'width'=> '60px', 'style' => 'border-left: 1px solid black;  border-right: 1px solid black; text-align:left;font-size:small;'),
																 array('data' => '',  'width' => '15px', 'style' => 'text-align:right;font-size:small;'),
																 array('data' => '- ' . $datasub->uraian . $statusdetilsub_pen,  'width' => '215px', 'style' => ' border-right: 1px solid black; text-align:left;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => '0', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => '0', 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => '0',  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => '0',  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																 array('data' => $datasub->unitjumlah . ' ' . $datasub->unitsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => $datasub->volumjumlah . ' ' . $datasub->volumsatuan, 'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:center;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' =>  apbd_fn($datasub->harga),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn($datasub->total),  'width' => '60px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),

																 array('data' => apbd_fn($datasub->total),  'width' => '70px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																 array('data' => apbd_fn1(100),  'width' => '35px', 'style' => ' border-right: 1px solid black; text-align:right;font-size:small;font-weight:lighter;font-style: italic;'),
																);	
													}
												}
											}															
															
										}
															 
									}

								}
							}												
						
						///////					 
						}
					}								 
										 
				////////
				}

	}
	
	$persen = apbd_hitungpersen($totalpen, $total);
	$rowsrek[] = array (
						 array('data' => 'JUMLAH BELANJA',  'width'=> '290px',  'colspan'=>'6',  'style' => 'border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black; border-top: 1px solid black; text-align:right;font-size:small; font-weight:bold;'),
						 array('data' => apbd_fn($totalpen),  'width' => '240px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 1px solid black; text-align:right;font-size:small; font-weight:bold;'),

						 array('data' => apbd_fn($total),  'width'=> '240px',  'colspan'=>'4',  'style' => 'border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black; border-top: 1px solid black; text-align:right;font-size:small; font-weight:bold;'),

						 array('data' => apbd_fn($total-$totalpen),  'width' => '70px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 1px solid black; text-align:right;font-size:small; font-weight:bold;'),
						 array('data' => apbd_fn1($persen),  'width' => '35px', 'style' => 'border-bottom: 1px solid black; border-right: 1px solid black; border-top: 1px solid black; text-align:right;font-size:small; font-weight:bold;'),

						 );
						 
	$opttbl = array('cellspacing'=>'1', 'cellpadding'=>'1', 'border' => '0');

	//$output .= theme_box('', apbd_theme_table($headersrek, $rowsrek, $opttbl));
	$output = createT($headersrek, $rowsrek,$opttbl);
	
	return $output;
}

?>