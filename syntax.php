<?php
/**
 * Syntax Plugin:
 * This plugin lists all users from the given groups in a tabel.
 * Syntax:
 * {{groupusers:<group1>[,group2[,group3...]]}}
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Dominik Eckelmann <eckelmann@cosmocode.de>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_groupusers extends DokuWiki_Syntax_Plugin {

    function groupusers() { }
    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Dominik Eckelmann, Julian Jacobi',
            'email'  => 'julian.jacobi@tu-bs.de',
            'date'   => '2015-05-06',
            'name'   => 'Groupusers Syntax plugin',
            'desc'   => 'Displays the users from one or more groups.',
            'url'    => ''
        );
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'normal';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 160;
    }

    function connectTo($mode) {
         $this->Lexer->addSpecialPattern('\{\{groupusers\>[^}]*?\}\}',$mode,'plugin_groupusers');
         $this->Lexer->addSpecialPattern('\{\{groupusers\|nomail\>[^}]*?\}\}',$mode,'plugin_groupusers');
    }

    function handle($match, $state, $pos, &$handler){
        $match = substr($match,13,-2);
        $data = array(null, $state, $pos);
		if (substr($match, 0, 7) == 'nomail>') 
        {
            $match = substr($match, 7);
            $data[] = 'nomail';
		}

        $match = explode(',',$match);
        
        $data[0] = $match;
		return $data;
    }

    function render($mode, &$renderer, $data) {
        global $auth;
        global $lang;
		global $PetMail; //thorsten@th-petersen.de ergänzt
 
        if (!method_exists($auth,"retrieveUsers")) return false;
        if($mode == 'xhtml'){
            $users = array();
            foreach ($data[0] as $grp) {
                $getuser = $auth->retrieveUsers(0,-1,array('grps'=>'^'.preg_quote($grp,'/').'$'));
                $users = array_merge($users,$getuser);
            }
            $renderer->doc .= $match.'<table class="inline">';
            $renderer->doc .= '<tr>';
            $renderer->doc .= '<th>'.$lang['user'].'</th>';
            $renderer->doc .= '<th>'.$lang['fullname'].'</th>';
 
            if (!in_array('nomail', $data))
			{
				$renderer->doc .= '<th>'.$lang['email'].'</th>';
			}
 
            $renderer->doc .= '</tr>';
            foreach ($users as $user => $info) {
                $renderer->doc .= '<tr>';
				$renderer->doc .= '<td>'; //thorsten@th-petersen.de ergänzt
                $renderer->internallink($user); ////thorsten@th-petersen.de Original: $renderer->doc .= '<td>'.htmlspecialchars($user).'</td>';
				$renderer->doc .= '</td>'; //thorsten@th-petersen.de ergänzt
                $renderer->doc .= '<td>'.hsc($info['name']).'</td>';
 
                if (!in_array('nomail', $data))
				{
                    $renderer->doc .= '<td>';
					$renderer->emaillink($info['mail']);
                    $renderer->doc .= '</td>';
					$PetMail.=$info['mail'].';'; //thorsten@th-petersen.de ergänzt
				}
 
                $renderer->doc .= '</tr>';
            }
            $renderer->doc .= '</table>';
            $renderer->doc .= "Email an alle:<br>"; //thorsten@th-petersen.de ergänzt#
            $renderer->emaillink($PetMail); //thorsten@th-petersen.de ergänzt
            $PetMail = ''; //important for multiple use (moz@e.mail.de)
            return true;
        }
        return false;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
