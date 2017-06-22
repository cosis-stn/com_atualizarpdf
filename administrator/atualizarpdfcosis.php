<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atualizarpdfcosis
 * @author     Clayton Alves Rodrigues <clayton.rodrigues@tesouro.gov.br>
 * @copyright  © 2016 Secretaria do Tesouro Nacional. Todos os direitos reservados.
 * @license    GNU General Public License versão 2 ou posterior; consulte o arquivo License. txt
 */
// No direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_atualizarpdfcosis')) {
    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Atualizarpdfcosis', JPATH_COMPONENT_ADMINISTRATOR);

$controller = JControllerLegacy::getInstance('Atualizarpdfcosis');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();