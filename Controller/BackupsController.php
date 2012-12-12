<?php
App::uses('BackuppableAppController', 'Backuppable.Controller');
/**
 * Backups Controller
 *
 * @property Backup $Backup
 */
class BackupsController extends AppController {

	public $uses = array('Backuppable.Backup');

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Backup->recursive = 0;
		$this->set('backups', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Backup->exists($id)) {
			throw new NotFoundException(__('Invalid backup'));
		}
		$options = array('conditions' => array('Backup.' . $this->Backup->primaryKey => $id));
		$this->set('backup', $this->Backup->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Backup->create();
			if ($this->Backup->save($this->request->data)) {
				$this->Session->setFlash(__('The backup has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The backup could not be saved. Please, try again.'));
			}
		}
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Backup->exists($id)) {
			throw new NotFoundException(__('Invalid backup'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Backup->save($this->request->data)) {
				$this->Session->setFlash(__('The backup has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The backup could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Backup.' . $this->Backup->primaryKey => $id));
			$this->request->data = $this->Backup->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @throws MethodNotAllowedException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Backup->id = $id;
		if (!$this->Backup->exists()) {
			throw new NotFoundException(__('Invalid backup'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Backup->delete()) {
			$this->Session->setFlash(__('Backup deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Backup was not deleted'));
		$this->redirect(array('action' => 'index'));
	}

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index() {
		$this->Backup->recursive = 0;
		$this->set('backups', $this->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Backup->exists($id)) {
			throw new NotFoundException(__('Invalid backup'));
		}
		$options = array('conditions' => array('Backup.' . $this->Backup->primaryKey => $id));
		$this->set('backup', $this->Backup->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Backup->create();
			if ($this->Backup->save($this->request->data)) {
				$this->Session->setFlash(__('The backup has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The backup could not be saved. Please, try again.'));
			}
		}
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->Backup->exists($id)) {
			throw new NotFoundException(__('Invalid backup'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Backup->save($this->request->data)) {
				$this->Session->setFlash(__('The backup has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The backup could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Backup.' . $this->Backup->primaryKey => $id));
			$this->request->data = $this->Backup->find('first', $options);
		}
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @throws MethodNotAllowedException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->Backup->id = $id;
		if (!$this->Backup->exists()) {
			throw new NotFoundException(__('Invalid backup'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Backup->delete()) {
			$this->Session->setFlash(__('Backup deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Backup was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
