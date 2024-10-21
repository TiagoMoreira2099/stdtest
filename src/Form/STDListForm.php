<?php

namespace Drupal\std\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rep\ListKeywordPage;
use Drupal\rep\Entity\StudyObject;
use Drupal\rep\Entity\MetadataTemplate;
use Drupal\std\Entity\Study;
use Drupal\std\Entity\StudyRole;
use Drupal\std\Entity\StudyObjectCollection;
use Drupal\std\Entity\VirtualColumn;

class STDListForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'std_list_form';
  }

  protected $list;

  protected $list_size;

  public function getList() {
    return $this->list;
  }

  public function setList($list) {
    return $this->list = $list; 
  }

  public function getListSize() {
    return $this->list_size;
  }

  public function setListSize($list_size) {
    return $this->list_size = $list_size; 
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $elementtype=NULL, $keyword=NULL, $page=NULL, $pagesize=NULL) {

    // GET TOTAL NUMBER OF ELEMENTS AND TOTAL NUMBER OF PAGES
    $this->setListSize(-1);
    if ($elementtype != NULL) {
      $this->setListSize(ListKeywordPage::total($elementtype, $keyword));
    }
    if (gettype($this->list_size) == 'string') {
      $total_pages = "0";
    } else { 
      if ($this->list_size % $pagesize == 0) {
        $total_pages = $this->list_size / $pagesize;
      } else {
        $total_pages = floor($this->list_size / $pagesize) + 1;
      }
    }

    // CREATE LINK FOR NEXT PAGE AND PREVIOUS PAGE
    if ($page < $total_pages) {
      $next_page = $page + 1;
      $next_page_link = ListKeywordPage::link($elementtype, $keyword, $next_page, $pagesize);
    } else {
      $next_page_link = '';
    }
    if ($page > 1) {
      $previous_page = $page - 1;
      $previous_page_link = ListKeywordPage::link($elementtype, $keyword, $previous_page, $pagesize);
    } else {
      $previous_page_link = '';
    }

    // RETRIEVE ELEMENTS
    $this->setList(ListKeywordPage::exec($elementtype, $keyword, $page, $pagesize));

    $class_name = "";
    $header = array();
    $output = array();    
    switch ($elementtype) {

      // DSG
      case "dsg":
        $class_name = "DSGs";
        $header = MetadataTemplate::generateHeader();
        $output = MetadataTemplate::generateOutput('dsg',$this->getList());    
        break;
  
      // DD
      case "dd":
        $class_name = "DDs";
        $header = MetadataTemplate::generateHeader();
        $output = MetadataTemplate::generateOutput('dd',$this->getList());    
        break;
  
      // SDD
      case "sdd":
        $class_name = "SDDs";
        $header = MetadataTemplate::generateHeader();
        $output = MetadataTemplate::generateOutput('sdd',$this->getList());    
        break;
  
      // DA
      case "da":
        $class_name = "DAs";
        $header = MetadataTemplate::generateHeader();
        $output = MetadataTemplate::generateOutput('da',$this->getList());    
        break;
  
      // STUDY
      case "study":
        $class_name = "Studies";
        $header = Study::generateHeader();
        $output = Study::generateOutput($this->getList());    
        break;
  
      // STUDY ROLE
      case "studyrole":
        $class_name = "Study Role";
        $header = StudyRole::generateHeader();
        $output = StudyRole::generateOutput($this->getList());    
        break;

      // STUDY OBJECT COLLECTION
      case "studyobjectcollection":
        $class_name = "Study Object Collections";
        $header = StudyObjectCollection::generateHeader();
        $output = StudyObjectCollection::generateOutput($this->getList());    
        break;

      // STUDY OBJECT
      case "studyobject":
        $class_name = "Study Object";
        $header = StudyObject::generateHeader();
        $output = StudyObject::generateOutput($this->getList());    
        break;

      // VIRTUAL COLUMN
      case "virtualcolumn":
        $class_name = "Virtual Column";
        $header = VirtualColumn::generateHeader();
        $output = VirtualColumn::generateOutput($this->getList());    
        break;

      default:
        $class_name = "Objects of Unknown Types";
    }

    // PUT FORM TOGETHER

    $form['title'] = [
      '#type' => 'item',
      '#title' => t('<h3>Available <font color="DarkGreen">' . $class_name . '</font></h3>'),
    ];

    $form['element_table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $output,
      '#empty' => t('No response options found'),
    ];

    $form['pager'] = [
      '#theme' => 'list-page',
      '#items' => [
        'page' => strval($page),
        'first' => ListKeywordPage::link($elementtype, $keyword, 1, $pagesize),
        'last' => ListKeywordPage::link($elementtype, $keyword, $total_pages, $pagesize),
        'previous' => $previous_page_link,
        'next' => $next_page_link,
        'last_page' => strval($total_pages),
        'links' => null,
        'title' => ' ',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */   
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}