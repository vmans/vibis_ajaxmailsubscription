<?php
namespace Vibis\VibisAjaxmailsubscription\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2019 Vincent Mans <info@vibis.nl>, Vibis
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

/**
 * SubscriptionController
 *
 */
class SubscriptionController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

    /**
     * defaultTable
     *
     * @var string
     */
    protected $defaultTable = 'tt_address';

    /**
     * tableId
     *
     * @var Array
     */
    protected $tableId = array('tt_address'=>1,'fe_users'=>2);

	protected $tables_mm=array(
		'fe_groups'=>array(
			'field'=>'usergroup',
			'table'=>'fe_users',
		),        
		'sys_dmail_category'=>array(
			'MM'=>'sys_dmail_ttaddress_category_mm',
			'table'=>'tt_address',
			'user_local'=>true,
		),
		'sys_dmail_group'=>array(
			'MM'=>'sys_dmail_group_mm',
			'user_local'=>false,
		),
		'sys_category'=>array(
			'MM'=>'sys_category_record_mm',
			'fieldname'=>'categories',
			'table'=>'tt_address',
			'user_local'=>false,
		),
	);

    /**
     * startAction
     *
     * @return void
     */
    public function startAction() {

        $assignedValues = array();
        $status = array('error'=>false, 'errorMsgs'=>array(), 'msgs'=>array());

        if ($this->settings['pageUid']==''){
            $status['error'] = true;
            $status['errorMsgs'][] = $this->getTransalation('controller.error.config.pageUid');
            $assignedValues['status'] = $status;
            $this->view->assignMultiple($assignedValues);	
            return;
        }

        // handle action
        $perform = GeneralUtility::_GP('p'); 
        $assignedValues['perform'] = $perform;
        if ($perform != ''){
            
            $status_isValidUrl = $this->isValidUrl();

            if ( $status_isValidUrl['error'] ){
                $status = $status_isValidUrl;
                $assignedValues['status'] = $status;
                $this->view->assignMultiple($assignedValues);	
                return;
            }else{

                $table = $status_isValidUrl['table'];
                $uid = $status_isValidUrl['data']['uid'];
                $user = array('uid' => $uid, 'table' => $table);
                switch ($perform) {
                    case 'confirm':
                        $updateColumns = array();
                        if ($table == 'tt_address'){
                            $updateColumns['hidden'] = 0;
                        }else if ($table == 'fe_users'){
                            $updateColumns['module_sys_dmail_newsletter'] = 1;
                        }
                        GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table)
                        ->update(
                            $table,
                            $updateColumns,
                            [ 'uid' => $uid ] // where
                        );
                        $this->joinList($user, $this->settings['flexform']['default_group']);
                        break;
                    case 'update':    
                        $subscription = GeneralUtility::_GP('subscription');
                        if (( $subscription == '' ) || ( $subscription == 0 )){
                            $unsubscription = true;
                            $this->leaveList($user);
                        }else{
                            $unsubscription = false;
                            $this->joinList($user, $this->settings['flexform']['default_group']);
                        }
                        
                        $first_name = GeneralUtility::_GP('first_name');
                        $middle_name= GeneralUtility::_GP('middle_name');
                        $last_name  = GeneralUtility::_GP('last_name');
                        $html       = GeneralUtility::_GP('html');
                        $updateColumns = array('first_name' => $first_name, 'middle_name' => $middle_name, 'last_name' => $last_name, 'module_sys_dmail_html' => $html);

                        $gender     = GeneralUtility::_GP('gender');
                        if ($gender){
                            $updateColumns['gender'] = $gender;
                        }
                        
                        if ($table == 'tt_address'){
                            $updateColumns['hidden'] = $unsubscription;
                        }else if ($table == 'fe_users'){
                            $updateColumns['module_sys_dmail_newsletter'] = !$unsubscription;
                        }

                        GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table)
                        ->update(
                            $table,
                            $updateColumns, // set 
                            [ 'uid' => $uid ] // where
                        );
                        $status['msgs'][] = 'Updated';
                        break;
                    case 'unsubscribe':
                        // unsubscribe link 
                        $updateColumns = array();
                        if ($table == 'tt_address'){
                            $updateColumns['hidden'] = 1;
                        }else if ($table == 'fe_users'){
                            $updateColumns['module_sys_dmail_newsletter'] = 0;
                        }

                        GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table)
                        ->update(
                            $table,
                            $updateColumns, // set 
                            [ 'uid' => $uid ] // where
                        );
                        $status['msgs'][] = 'Unsubscribed';
                        $user = array('uid' => $uid, 'table' => $table);
                        $this->leaveList($user);
                        break;                                         
                }        

                // view info                   
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
                $queryBuilder->getRestrictions()->removeAll();
                $statement = $queryBuilder
                ->select('*')
                ->from($table)
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid))
                )
                ->execute();
                $row = $statement->fetch();   

                $assignedValues['first_name']   = $row['first_name'];
                $assignedValues['middle_name']  = $row['middle_name'];
                $assignedValues['last_name']    = $row['last_name'];
                $assignedValues['gender']       = $row['gender'];
                $assignedValues['email']        = $row['email'];
                $assignedValues['html']         = $row['module_sys_dmail_html'] ? 'checked' : '' ;

                if ($table == 'tt_address'){
                    $assignedValues['subscription'] = ( $row['hidden'] || $row['deleted'] ) ? '' : 'checked' ;
                }else if ($table == 'fe_users'){
                    $assignedValues['subscription'] = $row['module_sys_dmail_newsletter'] ? 'checked' : '' ; 
                }                                
                
                $status_rid                     = $this->getRid($uid, $table);                
                if ($status_rid['error']){
                    $status = $status_rid;
                    $assignedValues['status'] = $status;
                    $this->view->assignMultiple($assignedValues);	
                    return;
                }else{
                    $rid                        = $status_rid['data']['tx_vibisajaxmailsubscription_rid'];
                    $assignedValues['u']        = $uid;                        
                    $assignedValues['t']        = $this->tableId[$table];                        
                    $assignedValues['a']        = $this->getAuthCode($rid, $uid);   
                    $assignedValues['edit']     = true;
                }
            }

        }// else default add email form shows up

        $assignedValues['status'] = $status;
        $this->view->assignMultiple($assignedValues);	
    }

	function joinList($user, $group){
        $lists=$this->splitGroup($group);
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
		foreach($lists as $list=>$items){
			if(isset($this->tables_mm[$list]) && (!isset($this->tables_mm[$list]['table']) || $this->tables_mm[$list]['table']==$user['table'])){
				if(isset($this->tables_mm[$list]['MM'])){
					foreach($items as $item){
						if($this->tables_mm[$list]['user_local']){
							$insert=array(
								'uid_local'=>$user['uid'],
								'uid_foreign'=>$item,
							);
						}else{
							$insert=array(
								'uid_local'=>$item,
								'uid_foreign'=>$user['uid'],
							);
						}
						if(isset($this->tables_mm[$list]['table'])) {
							$insert['tablenames']=$this->tables_mm[$list]['table'];
						} else {
							$insert['tablenames']=$user['table'];
						}
                        if(isset($this->tables_mm[$list]['fieldname'])) $insert['fieldname']=$this->tables_mm[$list]['fieldname'];
                        
                        $databaseConnectionForTable = $connectionPool->getConnectionForTable($this->tables_mm[$list]['MM']);
                        $databaseConnectionForTable->insert(
                            $this->tables_mm[$list]['MM'],
                            $insert
                        );
					}
				}elseif(isset($this->tables_mm[$list]['field'])){
                    $field=$this->tables_mm[$list]['field'];                    
                    $row = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getConnectionForTable($user['table'])
                    ->select(
                        [$field], // fields to select
                        $user['table'], // from
                        [ 'uid' => (int)$user['uid'] ] // where
                    )
                    ->fetch();                                    
                    
                    $old=explode(',',$row[$field]);
					$new=$old ? array_merge($old,$items) : $items;
					$update=array(
						$field=>implode(',',$new)
					);
                    GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($user['table'])
                    ->update(
                        $user['table'],
                        $update, // set 
                        [ 'uid' => (int)$user['uid'] ] // where
                    );                    
				}
			}
		}
	}

	function leaveList($user){
		foreach($this->tables_mm as $list=>$conf){
			if(isset($conf['MM']) && (!isset($conf['table']) || $conf['table']==$user['table'])){
				if($conf['user_local']){
                    $where_col = 'uid_local';
				}else{
                    $where_col = 'uid_foreign';
				}
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($user['table']);
                $affectedRows = $queryBuilder
                   ->delete( $conf['MM'] )
                   ->where(
                      $queryBuilder->expr()->eq($where_col, $queryBuilder->createNamedParameter($user['uid'])),
                      $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($user['table'])),
                        $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter(''))
                      )
                   )
                   ->execute();
			}
		}
	}

	function splitGroup($group){
		$groups=explode(',',$group);
		foreach($groups as $group){
			$item=GeneralUtility::revExplode('_',$group,2);
			$ret[$item[0]][]=$item[1];
		}
		return($ret);
	}

    
    /**
     * statusAction
     *
     * @return void
     */
    public function statusAction() {

        $status = array('error'=>false, 'errorMsgs'=>array(), 'msgs'=>array());
        $subscriptionStatus = '';

        $email = GeneralUtility::_GP('email');
        if ($email && GeneralUtility::validEmail($email)) {    
            $status_findEmail =  $this->findEmail($email);      
            $subscriptionStatus = $status_findEmail['subscriptionStatus'];
            $table = $status_findEmail['table'];
            switch ($subscriptionStatus) {
                case 'notFound':
                    $uid = $this->addEmail($email);
                    $status_sendconformationEmail = $this->sendconformationEmail($email, $uid, $this->defaultTable, 'confirm');
                    if($status_sendconformationEmail['error']){
                        $status = $status_sendconformationEmail;
                        $assignedValues['status'] = $status;
                        $this->view->assignMultiple($assignedValues);	
                        return;
                    }else{
                        $status['msgs'][] = $this->getTransalation('controller.msg.confirmMailSent');
                    }
                    break;
                case 'subscribed':
                    // confirm owner                   
                    $status_sendconformationEmail = $this->sendconformationEmail($email, $status_findEmail['data']['uid'], $table, 'auth');
                    if($status_sendconformationEmail['error']){
                        $status = $status_sendconformationEmail;
                        $assignedValues['status'] = $status;
                        $this->view->assignMultiple($assignedValues);	
                        return;
                }else{
                        $status['msgs'][] = $this->getTransalation('controller.msg.confirmMailSentOwner');
                    }
                    break;
                case 'unsubscribed':
                    // send conformation mail to verify owner and susbcribe 
                    $status_sendconformationEmail = $this->sendconformationEmail($email, $status_findEmail['data']['uid'], $table, 'auth');
                    if($status_sendconformationEmail['error']){
                        $status = $status_sendconformationEmail;
                        $assignedValues['status'] = $status;
                        $this->view->assignMultiple($assignedValues);	
                        return;
                }else{
                        $status['msgs'][] = $this->getTransalation('controller.msg.confirmMailSub');
                    }
                    break;
            }


        }else{
            $status['error'] = true;
            $status['errorMsgs'][] = self::getTransalation('controller.error.invalidEmail');
            $assignedValues['status'] = $status;
            $this->view->assignMultiple($assignedValues);	
            return;
        }

        $assignedValues = [
            'email'                 => $email,
            'status'                => $status,
            'subscriptionStatus'    => $subscriptionStatus,
        ];
        $this->view->assignMultiple($assignedValues);	
    }

    function isValidUrl(){
        $status = array('error'=>false, 'errorMsgs'=>array(), 'techMsgs'=>array(), 'msgs'=>array());

        $authCode = GeneralUtility::_GP('a');
        $timeHex = substr($authCode,0,8);

        if( ($this->settings['authcode_expiration_time'] != 0) 
            && ((time() - hexdec($timeHex)) > ($this->settings['authcode_expiration_time'] * 60))) {
                $status['error'] = true;
                $status['errorMsgs'][] = self::getTransalation('controller.error.authExpired');
                return $status; 
        }

        if (GeneralUtility::_GP('t')){
            $table = array_search(GeneralUtility::_GP('t'), $this->tableId);
        }
            
        if (GeneralUtility::_GP('u')){
            $uid = GeneralUtility::_GP('u');
        }    

        if ( $table && $uid ){
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                            ->getQueryBuilderForTable($table);
            $queryBuilder->getRestrictions()->removeAll();
            $statement = $queryBuilder
               ->select('*')
               ->from($table)
               ->where(
                  $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid))
               )
               ->execute();
            $row = $statement->fetch();
            if ($row){
                if ( $row['tx_vibisajaxmailsubscription_rid'] == '' ){
                    $status['error'] = true;
                    $status['errorMsgs'][] = self::getTransalation('controller.error.authCode');
                    $status['techMsgs'][] = 'tx_vibisajaxmailsubscription_rid is blank';
                    return $status; 
                }

                // validate hash              
                $rid = $row['tx_vibisajaxmailsubscription_rid'];  
                $reAuthCode = $this->getAuthCode($rid, $uid, $timeHex);
                if ($authCode == $reAuthCode) { 

                    $status['table']    = $table;
                    $status['data']     = $row;

                    GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table)
                    ->update(
                        $table,
                        [ 'tx_vibisajaxmailsubscription_rid' => '' ], // set
                        [ 'uid' => $uid ] // where
                    );
                    return $status; 
                }else{ 
                    $status['error'] = true;
                    $status['errorMsgs'][] = self::getTransalation('controller.error.authCode');
                    $status['techMsgs'][] = 'authCode did not match';
                    return $status; 
                    }
            }else{ 
                $status['error'] = true;
                $status['errorMsgs'][] = self::getTransalation('controller.error.authCode');
                $status['techMsgs'][] = 'Uid='. $uid .' not found in table: '.$table;
                return $status; 
            }
        }else{ 
            $status['error'] = true;
            $status['errorMsgs'][] = self::getTransalation('controller.error.authCode');
            $status['techMsgs'][] = 'currentTable, uid value missing';
            return $status; 
        }

    }

    function addEmail($email){

        if (!$this->defaultTable){ return false; }

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $databaseConnectionForTable = $connectionPool->getConnectionForTable($this->defaultTable);
        $databaseConnectionForTable->insert(
            $this->defaultTable,
            [
                'email'     => $email,
                'hidden'    => 1,
                'pid'       => $this->settings['flexform']['storagePid'],
                'tx_vibisajaxmailsubscription_rid' => '',
            ]
        );
        $lastInsertId = (int)$databaseConnectionForTable->lastInsertId($this->defaultTable);
        return $lastInsertId;
    }


    function sendconformationEmail($email, $uid, $table, $type = 'confirm') {
        $status_getLink = $this->getLink('confirm', $uid, $table);  

        if ($status_getLink['error']) return $status_getLink;

        $validateEmailUrl = $status_getLink['data']['url'];
        $mail = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Mail\MailMessage::class);
        
        $mail->setFrom( array($this->settings['flexform']['mail_from'] => $this->settings['flexform']['mail_from_name']) );
        $mail->setTo(array($email));
        if ($type == 'confirm'){
            $mail->setSubject($this->getTransalation('mail.confirm.subject'));
            $mail->setBody( $this->getTransalation('mail.confirm.body', array($validateEmailUrl) ) );
        }else{
            $mail->setSubject($this->getTransalation('mail.auth.subject'));
            $mail->setBody( $this->getTransalation('mail.auth.body', array($validateEmailUrl) ) );
        }
       
        $mail->send();        
        self::logExec("To confirm your email please visit: $validateEmailUrl");
    }

    function getLink($perform, $uid, $table){
        $status = array('error'=>false, 'errorMsgs'=>array(), 'techMsgs'=>array(), 'msgs'=>array());
        if (!$uid || !$table) {
            $status['error'] = true;
            $status['errorMsgs'][] = self::getTransalation('controller.error.technicalError'); ;
            $status['techMsgs'][] = "uid/table missing in getLink($perform, $uid, $table)";
            return $status;
        }

        $status_rid = $this->getRid($uid, $table);
        if ($status_rid['error']){
            return $status_rid;
        }
        $rid = $status_rid['data']['tx_vibisajaxmailsubscription_rid'];

        $authCode = $this->getAuthCode($rid, $uid);        

        $urlArguments = [
            'a' => $authCode,
            't' => $this->tableId[$table],
            'u' => $uid,
            'L' => $GLOBALS['TSFE']->sys_language_uid,
            'p' => $perform,
        ];
        $url = $this->uriBuilder->reset()
            ->setTargetPageUid($GLOBALS['TSFE']->id)
            ->setCreateAbsoluteUri(TRUE)
            ->setArguments($urlArguments)
            ->setUseCacheHash(false)
            ->build();

        $status['data']['url'] = $url;
        return $status;
    }

    function getRid($uid, $table){
        $status = array('error'=>false, 'errorMsgs'=>array(), 'techMsgs'=>array(), 'msgs'=>array());

        if( $table && $uid ){

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                            ->getQueryBuilderForTable($table);
            $queryBuilder->getRestrictions()->removeAll();
            $statement = $queryBuilder
               ->select('tx_vibisajaxmailsubscription_rid')
               ->from($table)
               ->where(
                  $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid))
               )
               ->execute();
            $row = $statement->fetch();
            if ($row){
                if ($row['tx_vibisajaxmailsubscription_rid'] != ''){
                    $status['data']['tx_vibisajaxmailsubscription_rid'] = $row['tx_vibisajaxmailsubscription_rid'];
                    return $status;     
                }
            }else{
                $status['error'] = true;
                $status['errorMsgs'][] = self::getTransalation('controller.error.technicalError'); 
                $status['techMsgs'][] = "no row with uid=$uid found in table=" . $table;
                return $status; 
            }

            // create rid
            $rid = self::random_str(11); 
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
            $queryBuilder
            ->update($table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid))
             )
             ->set('tx_vibisajaxmailsubscription_rid', $rid);
             $affectedRows = $queryBuilder->execute();                    
            $status['data']['tx_vibisajaxmailsubscription_rid'] = $rid;
            return $status;     

        }else{ 
            $status['error'] = true; 
            $status['errorMsgs'][] = self::getTransalation('controller.error.technicalError'); 
            $status['techMsgs'][] = ' ($table && $uid) is not true '; 
            $status['techMsgs'][] = " values: ($table && $uid)"; 
            return $status; 
        }
    }

    function getAuthCode($rid, $uid, $timeHex = ''){
        if (!$timeHex)
        $timeHex = str_pad(dechex(time()), 8, '0', STR_PAD_LEFT);
        $stdAuthCode = GeneralUtility::stdAuthCode($uid, 'uid');
        $authCode = $timeHex . substr( md5( $timeHex . $stdAuthCode . $rid ), 0, 11 );
        return $authCode;
    }

    /*
    * look for the email in fe_users and tt_address 
    * returns  notFound, subscribed, unsubscribed
    */ 
    function findEmail($email) {

        $status = array('error'=>false, 'errorMsgs'=>array(), 'techMsgs'=>array(), 'msgs'=>array(), 'data'=>array());

        $this->userInfo = array();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_address');
        $queryBuilder->getRestrictions()->removeAll();
        $statement = $queryBuilder
           ->select('*')
           ->from('tt_address')
           ->where(
              $queryBuilder->expr()->eq('email', $queryBuilder->createNamedParameter($email))
           )
           ->execute();
        $row = $statement->fetch();        
        if ($row){
            $status['table'] = 'tt_address';
            $status['data'] = $row;

            if( $row['hidden'] || $row['deleted'] ){
                $status['subscriptionStatus'] = 'unsubscribed';
            }else{
                $status['subscriptionStatus'] = 'subscribed';
            }            
            
            return $status;

        }else{
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
            $statement = $queryBuilder
               ->select('uid', 'name', 'module_sys_dmail_newsletter')
               ->from('fe_users')
               ->where(
                  $queryBuilder->expr()->eq('email', $queryBuilder->createNamedParameter($email))
               )
               ->execute();
            $row = $statement->fetch();   
            if ($row){
                $status['table'] = 'fe_users';
                $status['data'] = $row;

                if( $row['module_sys_dmail_newsletter'] ){
                    $status['subscriptionStatus'] = 'subscribed';
                }else{
                    $status['subscriptionStatus'] = 'unsubscribed';
                }
                return $status;

            }else{    
                $status['subscriptionStatus'] = 'notFound';
                return $status;
            }    
        }
    }

    /**
     * Generate a random string, using a cryptographically secure 
     * pseudorandom number generator
     * 
     * @param int $length      How many characters do we want?
     * @param string $keyspace A string of all possible characters
     *                         to select from
     * @return string
     */
    function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    function getTransalation($key, $args = array()){ return LocalizationUtility::translate("LLL:EXT:vibis_ajaxmailsubscription/Resources/Private/Language/locallang_db.xlf:$key", 'vibis_ajaxmailsubscription', $args); }

	/*------------------------------------------------------------*/
	function logExec($var, $title=''){
        return;
        if (is_array($var)){ $var = print_r($var, true); }	
        file_put_contents(PATH_site . 'fileadmin/log.txt',
        print_r(
          "\r\r"  
        . date('Y-m-d::h:i:s') . $title
        . "\r~~~~~~~~~~~\r" 
        . $var, true), FILE_APPEND);
    }
}