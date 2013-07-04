<?php

class Cursuri extends Controller {

	public function __construct() {
		parent::__construct();
		$this->incarcaModel('cursuri');
	}
	
	public function index() {
		$data = array();
		
		$this->view->render('cursuri', $data, false);
	}
	
	public function cursurile_mele() {
		$data = array();
		if(are_rol('administrator')) {
			$data['cursuri'] = $this->cursuri->lista_cursuri_administrare();
		} else {
			$data['cursuri'] = $this->cursuri->lista_cursuri_utilizator((int) $_SESSION['id_utilizator']);
		}
		$this->view->render('cursurile_mele', $data, false);
	}
	
	public function curs($id_curs) {
		$data = array();
		if(isset($_SESSION['mesaj'])) {
			$data['mesaj'] = $_SESSION['mesaj'];
			unset($_SESSION['mesaj']);
		}
		$data['id_curs'] = $id_curs;
		$data['detalii_curs'] = $this->cursuri->detalii_curs($id_curs);
		$data['discutii'] = $this->cursuri->discutii_forum($data['detalii_curs']['id_forum']);
		$this->view->render('curs', $data, false);
	}
	
	public function categorii() {
		$data = array();
		$data['categorii_cursuri'] = $this->cursuri->categorii_format_tree();
		$this->view->render('categorii_cursuri', $data, false);
	}
	
	public function adauga_categorie() {
		$data = array();
		if( isset($_POST['salveaza']) ) {
			$validare_formular = new Validare_Formular;
			$validare_formular->set_rules('titlu', 'TITLU', 'trim|obligatoriu');
            if( $validare_formular->run() == FALSE ) {
                // eroare
                $data['mesaj'] = $validare_formular->error_string('<div class="alert alert-error">', '</div>');
            } else {
                // salveaza datele
                $inserare = array(
                    'id_parinte' => $_POST['parinte'],
                    'titlu' => $_POST['titlu'],
                    'descriere' => $_POST['descriere'],
                );
                // datele au fost introduse
                if( $this->cursuri->adauga_categorie($inserare) ) {
                    unset($_POST);
                    $data['mesaj'] = '<div class="alert alert-success">Categoria a fost adaugata! &nbsp;&nbsp;&nbsp; <a class="btn" href="'.URL.'index.php?url=cursuri/categorii">Inapoi la categorii</a></div>';
                } else {
                    $data['mesaj'] = '<div class="alert alert-error">A intervenit o eroare in momentul salvarii datelor! <br />'.mysql_error().'</div>';
                }
            }
		}
		$data['categorii_cursuri'] = $this->cursuri->categorii_format_tree();
		$this->view->render('adauga_categorie', $data, false);
	}
	
	public function modifica_categorie($id_categorie) {
		$data = array();
		if( isset($_POST['salveaza']) ) {
			$validare_formular = new Validare_Formular;
			$validare_formular->set_rules('titlu', 'TITLU', 'trim|obligatoriu');
            if( $validare_formular->run() == FALSE ) {
                // eroare
                $data['mesaj'] = $validare_formular->error_string('<div class="alert alert-error">', '</div>');
            } else {
                // salveaza datele
                $update = array(
                    'id_categorie' => $id_categorie,
                    'id_parinte' => $_POST['parinte'],
                    'titlu' => $_POST['titlu'],
                    'descriere' => $_POST['descriere'],
                );
                // datele au fost introduse
                if( $this->cursuri->modifica_categorie($update) ) {
                    unset($_POST);
                    $data['mesaj'] = '<div class="alert alert-success">Categoria a fost modificata! &nbsp;&nbsp;&nbsp; <a class="btn" href="'.URL.'index.php?url=cursuri/categorii">Inapoi la categorii</a></div>';
                } else {
                    $data['mesaj'] = '<div class="alert alert-error">A intervenit o eroare in momentul salvarii datelor! <br />'.mysql_error().'</div>';
                }
            }
		}
		$data['id_categorie'] = $id_categorie;
		$data['detalii_categorie'] = $this->cursuri->detalii_categorie($id_categorie);
		$data['categorii_cursuri'] = $this->cursuri->categorii_format_tree();
		$this->view->render('modifica_categorie', $data, false);
	}
	
	public function adauga_curs() {
		$data = array();
		if( isset($_POST['salveaza']) ) {
			$validare_formular = new Validare_Formular;
			$validare_formular->set_rules('profesor', 'PROFESOR', 'trim|obligatoriu');
			$validare_formular->set_rules('categorie', 'CATEGORIE', 'trim|obligatoriu');
			$validare_formular->set_rules('titlu', 'TITLU', 'trim|obligatoriu');
			$validare_formular->set_rules('data_inceput', 'DATA INCEPUT', 'trim|obligatoriu');
            if( $validare_formular->run() == FALSE ) {
                // eroare
                $data['mesaj'] = $validare_formular->error_string('<div class="alert alert-error">', '</div>');
            } else {
                // salveaza datele
                $insert = array(
                    'id_categorie' => $_POST['categorie'],
                    'id_responsabil' => $_POST['profesor'],
                    'titlu' => $_POST['titlu'],
                    'data_inceput' => $_POST['data_inceput'],
                    'descriere' => $_POST['descriere'],
                );
                // datele au fost introduse
                if( $id_curs = $this->cursuri->adauga_curs($insert) ) {
                    unset($_POST);
                    $data['mesaj'] = '<div class="alert alert-success">Cursul a fost adaugat! &nbsp;&nbsp;&nbsp; <a class="btn" href="'.URL.'index.php?url=cursuri/curs/'.$id_curs.'">Vezi pagina curs</a></div>';
                } else {
                    $data['mesaj'] = '<div class="alert alert-error">A intervenit o eroare in momentul salvarii datelor! <br />'.mysql_error().'</div>';
                }
            }
		}
		$this->incarcaModel('utilizator');
		$data['profesori'] = $this->utilizator->lista_profesori();
		$data['categorii_cursuri'] = $this->cursuri->categorii_format_tree();
		$this->view->render('adauga_curs', $data, false);
	}
	
	public function adauga_subiect_discutie($id) {
		$data = array();
		$id_exp = explode('_', $id);
		$id_curs  = $id_exp[0];
		$id_forum = $id_exp[1];
		if( isset($_POST['salveaza']) ) {
			$validare_formular = new Validare_Formular;
			$validare_formular->set_rules('subiect', 'SUBIECT', 'trim|obligatoriu');
            if( $validare_formular->run() == FALSE ) {
                // eroare
                $data['mesaj'] = $validare_formular->error_string('<div class="alert alert-error">', '</div>');
            } else {
                // salveaza datele
                $insert = array(
                    'id_forum' => $id_forum,
                    'titlu' => $_POST['subiect'],
					'id_utilizator' => $_SESSION['id_utilizator']
                );
                // datele au fost introduse
                if( $this->cursuri->adauga_subiect_discutie($insert) ) {
                    unset($_POST);
                    $data['mesaj'] = '<div class="alert alert-success">Subiectul a fost adaugat! &nbsp;&nbsp;&nbsp; <a class="btn" href="'.URL.'index.php?url=cursuri/curs/'.$id_curs.'">Vezi pagina curs</a></div>';
                } else {
                    $data['mesaj'] = '<div class="alert alert-error">A intervenit o eroare in momentul salvarii datelor! <br />'.mysql_error().'</div>';
                }
            }
		}
		$data['id_forum'] = $id_forum;
		$data['id_curs'] = $id_curs;
		$this->view->render('adauga_subiect_discutie', $data, false);
	}
	
	public function modifica_subiect_discutie($id) {
		$data = array();
		$id_exp = explode('_', $id);
		$id_curs  = $id_exp[0];
		$id_subiect = $id_exp[1];
		if( isset($_POST['salveaza']) ) {
			$validare_formular = new Validare_Formular;
			$validare_formular->set_rules('subiect', 'SUBIECT', 'trim|obligatoriu');
            if( $validare_formular->run() == FALSE ) {
                // eroare
                $data['mesaj'] = $validare_formular->error_string('<div class="alert alert-error">', '</div>');
            } else {
                // salveaza datele
                $update = array(
                    'id_subiect' => $id_subiect,
                    'titlu' => $_POST['subiect']
                );
                // datele au fost introduse
                if( $this->cursuri->modifica_subiect_discutie($update) ) {
                    unset($_POST);
                    $data['mesaj'] = '<div class="alert alert-success">Subiectul a fost modificat! &nbsp;&nbsp;&nbsp; <a class="btn" href="'.URL.'index.php?url=cursuri/curs/'.$id_curs.'">Vezi pagina curs</a></div>';
                } else {
                    $data['mesaj'] = '<div class="alert alert-error">A intervenit o eroare in momentul salvarii datelor! <br />'.mysql_error().'</div>';
                }
            }
		}
		$data['id_subiect'] = $id_subiect;
		$data['id_curs'] = $id_curs;
		$data['detalii_subiect'] = $this->cursuri->subiect_discutie($id_subiect);
		$this->view->render('modifica_subiect_discutie', $data, false);
	}
	
	public function postari($id) {
		$data = array();
		$id_exp = explode('_', $id);
		$id_curs  = $id_exp[0];
		$id_subiect = $id_exp[1];
		$data['detalii_curs'] = $this->cursuri->detalii_curs($id_curs);
		$data['subiect'] = $this->cursuri->subiect_discutie($id_subiect);
		$data['postari'] = $this->cursuri->postari_subiect($id_subiect);
		$data['id_curs'] = $id_curs;
		$data['id_subiect'] = $id_subiect;
		if(isset($_SESSION['mesaj'])) {
			$data['mesaj'] = $_SESSION['mesaj'];
			unset($_SESSION['mesaj']);
		}
		$this->view->render('discutii', $data, false);
	}
	
	public function adauga_postare($id) {
		$data = array();
		$id_exp = explode('_', $id);
		$id_curs  = $id_exp[0];
		$id_subiect = $id_exp[1];
		if( isset($_POST['salveaza']) ) {
			$validare_formular = new Validare_Formular;
			$validare_formular->set_rules('raspuns', 'RASPUNS', 'trim|obligatoriu');
            if( $validare_formular->run() == FALSE ) {
                // eroare
                $data['mesaj'] = $validare_formular->error_string('<div class="alert alert-error">', '</div>');
            } else {
                // salveaza datele
                $insert = array(
                    'id_discutie' => $id_subiect,
					'id_utilizator' => $_SESSION['id_utilizator'],
                    'raspuns' => $_POST['raspuns'],
                );
                // datele au fost introduse
                if( $this->cursuri->adauga_postare($insert) ) {
                    unset($_POST);
                    $_SESSION['mesaj'] = '<div class="alert alert-success">Postarea a fost adaugata!</div>';
					header("Location: index.php?url=cursuri/postari/{$id_curs}_{$id_subiect}");
					exit();
				} else {
                    $data['mesaj'] = '<div class="alert alert-error">A intervenit o eroare in momentul salvarii datelor! <br />'.mysql_error().'</div>';
                }
            }
		}
		$data['id_curs'] = $id_curs;
		$data['id_subiect'] = $id_subiect;
		$this->view->render('adauga_postare', $data, false);
	}
	
	public function sterge_subiect_discutie($id) {
		$data = array();
		$id_exp = explode('_', $id);
		$id_curs  = $id_exp[0];
		$id_subiect = $id_exp[1];
		if($this->cursuri->sterge_subiect_discutie($id_subiect)) {
			$_SESSION['mesaj'] = '<div class="alert alert-success">Subiectul a fost sters!</div>';
		} else {
			$_SESSION['mesaj'] = '<div class="alert alert-error">A intervenit o eroare in momentul stergerii datelor!</div>';
		}
		header("Location: index.php?url=cursuri/curs/{$id_curs}");
	}
	
	public function adauga_eveniment($id_curs) {
		$data = array();
		if( isset($_POST['salveaza']) ) {
			$validare_formular = new Validare_Formular;
			$validare_formular->set_rules('titlu', 'TITLU', 'trim|obligatoriu');
			$validare_formular->set_rules('data', 'DATA EVENIMENT', 'trim|obligatoriu');
            if( $validare_formular->run() == FALSE ) {
                // eroare
                $data['mesaj'] = $validare_formular->error_string('<div class="alert alert-error">', '</div>');
            } else {
                // salveaza datele
                $insert = array(
                    'id_curs' => $id_curs,
                    'titlu' => $_POST['titlu'],
                    'data_eveniment' => $_POST['data'],
					'id_utilizator' => $_SESSION['id_utilizator']
                );
                // datele au fost introduse
                if( $this->cursuri->adauga_eveniment($insert) ) {
                    unset($_POST);
                    $data['mesaj'] = '<div class="alert alert-success">Evenimentul a fost adaugat! &nbsp;&nbsp;&nbsp; <a class="btn" href="'.URL.'index.php?url=cursuri/cursurile_mele">Vezi lista cursuri</a></div>';
                } else {
                    $data['mesaj'] = '<div class="alert alert-error">A intervenit o eroare in momentul salvarii datelor! <br />'.mysql_error().'</div>';
                }
            }
		}
		$data['id_curs'] = $id_curs;
		$this->view->render('adauga_eveniment', $data, false);
	}
	
	public function adauga_utilizatori_curs($id_curs) {
		$data = array();
		$this->incarcaModel('utilizator');
		$data['id_curs'] = $id_curs;
		$data['studenti'] = $this->utilizator->lista_studenti();
		$utilizatori = $this->cursuri->lista_utilizatori_curs($id_curs);
		$data['utilizatori_curs'] = array();
		if(!empty($utilizatori)) {
		foreach($utilizatori as $utilizator) {
			$data['utilizatori_curs'][] = $utilizator['id_utilizator'];
		}
		}
		$this->view->render('adauga_utilizatori_curs', $data, false);
	}
	
	public function ajax_adauga_utilizator_curs() {
		header('Content-Type: application/json');
        if( isset($_POST['id_curs']) && isset($_POST['id_utilizator']) ) {
            $id_curs = (int) $_POST['id_curs'];
            $id_utilizator = (int) $_POST['id_utilizator'];
            $activ = (int) $_POST['activat'];  
            if( $this->cursuri->adauga_utilizator_curs( $id_curs, $id_utilizator, $activ ) ) {
                echo json_encode(array('error' => 'false', 'mesaj' => 'ok')); 
            } else {
                echo json_encode(array('error' => 'true', 'mesaj' => 'A intervenit o eroare in momentul salvarii datelor!')); 
            }
        }
        exit();
	}
	
	public function calendar() {
		$data = array();
		$data['evenimente'] = $this->cursuri->evenimente_student($_SESSION['id_utilizator']);
		$this->view->render('calendar', $data, false);
	}

} 