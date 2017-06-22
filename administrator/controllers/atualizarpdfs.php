<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atualizarpdfcosis
 * @author     Clayton Alves Rodrigues <clayton.rodrigues@tesouro.gov.br>
 * @copyright  © 2016 STN/COSIS. Todos os direitos reservados.
 * @license    GNU General Public License versão 2 ou posterior; consulte o arquivo License. txt
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');
jimport('tcpdf.tcpdf');
jimport('tcpdf.library.tcpdf');
require_once(JPATH_LIBRARIES . '\mpdf\mpdf.php');

use Joomla\Utilities\ArrayHelper;

/**
 * Atualizarpdfs list controller class.
 *
 * @since  1.6
 */
class AtualizarpdfcosisControllerAtualizarpdfs extends JControllerAdmin {

    /**
     * Method to clone existing Atualizarpdfs
     *
     * @return void
     */
    public function duplicate() {
        // Check for request forgeries
        Jsession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get id(s)
        $pks = $this->input->post->get('cid', array(), 'array');

        try {
            if (empty($pks)) {
                throw new Exception(JText::_('COM_ATUALIZARPDFCOSIS_NO_ELEMENT_SELECTED'));
            }

            ArrayHelper::toInteger($pks);
            $model = $this->getModel();
            $model->duplicate($pks);
            $this->setMessage(Jtext::_('COM_ATUALIZARPDFCOSIS_ITEMS_SUCCESS_DUPLICATED'));
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect('index.php?option=com_atualizarpdfcosis&view=atualizarpdfs');
    }

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    Optional. Model name
     * @param   string  $prefix  Optional. Class prefix
     * @param   array   $config  Optional. Configuration array for model
     *
     * @return  object	The Model
     *
     * @since    1.6
     */
    public function getModel($name = 'atualizarpdf', $prefix = 'AtualizarpdfcosisModel', $config = array()) {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));

        return $model;
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return  void
     *
     * @since   3.0
     */
    public function saveOrderAjax() {
        // Get the input
        $input = JFactory::getApplication()->input;
        $pks = $input->post->get('cid', array(), 'array');
        $order = $input->post->get('order', array(), 'array');

        // Sanitize the input
        ArrayHelper::toInteger($pks);
        ArrayHelper::toInteger($order);

        // Get the model
        $model = $this->getModel();

        // Save the ordering
        $return = $model->saveorder($pks, $order);

        if ($return) {
            echo "1";
        }

        // Close the application
        JFactory::getApplication()->close();
    }

    public function gerarPdf() {
        $idCategoria = ( $this->input->get('idCategoria') ) ? $this->input->get('idCategoria') : '';
        $idAtualizacao = ( $this->input->get('idAtualizacao') ) ? $this->input->get('idAtualizacao') : '';

        $this->gerarMPdf($idCategoria);
        $this->gravarAtualizacao($idAtualizacao);
        JFactory::getApplication()->enqueueMessage('Versão PDF atualizado com sucesso!');
        $this->setRedirect('index.php?option=com_atualizarpdfcosis&view=atualizarpdfs');
    }

    /**
     * Método para gerar pdf usando a biblioteca MPdf
     */
    public function gerarMPdf($idCategoria = '') {

        $html = '';
        $options = array();
        $categories = JCategories::getInstance('Content', $options);
        $category = $categories->get($idCategoria);
        JRequest::setVar('titulo', $category->title);

        // 15=margem esquerda, 15=margem direita, 30=margem topo, 18=margem rodape
        $mpdf = new mPDF('', '', 11, 'ctimes', '15', '15', '30', '18');
        $mpdf->useSubstitutions = true;
        $obArtigoCapa = self::recuperarArtigosPorAlias('capa-' . $category->alias);

        // Área que será utilizada para exportação
        $mpdf->SetDisplayMode('fullpage');

        // Caminho do CSS externo
        $stylesheet = file_get_contents('components/com_atualizarpdfcosis/assets/css/pdf.css');

        // Incorpora o ficheiro CSS
        $mpdf->WriteHTML($stylesheet, 1);

        // Imprime a Capa
        if ($obArtigoCapa) {
            $htmlCapa = AtualizarpdfcosisControllerAtualizarpdfs::corrigirCaminhoImagens($obArtigoCapa->introtext);
            $mpdf->WriteHTML($htmlCapa);
        }

        // chama a funçao que monta o cabecalho
        self::cabecalhoMPdf($mpdf);

        // Imprime o Sumário
        self::sumarioMPdf($mpdf);

        // chama a função que monta o rodape
        self::rodapeMPdf($mpdf);

        // Escreve o conteúdo da variável $html
        self::gerarHtmlMPdf($category, $mpdf);

        // Exporta o resultado para PDF
        $mpdf->Output('../modules/mod_pdf_manual/pdf/' . $category->alias . '.pdf', 'F');
    }

    public function gravarAtualizacao($idAtualizacao) {
        // Gravar última atualização
        $user = JFactory::getUser();
        $dataAtualizacao = new JDate('now');
        $UltimaAtualizacao = new stdClass();
        $UltimaAtualizacao->id = $idAtualizacao;
        $UltimaAtualizacao->checked_out_time = $dataAtualizacao->toSql();
        $UltimaAtualizacao->modified_by = $user->id;
        $db = JFactory::getDbo();

        $result = $db->updateObject('#__atualizarpdfcosis', $UltimaAtualizacao, 'id');
    }

    public static function sumarioMPdf(&$mpdf) {
        $mpdf->TOCpagebreak(
                $tocfont = 'ctimes', $tocfontsize = '11', $tocindent = '', $TOCusePaging = true, $TOCuseLinking = true, $toc_orientation = '', $toc_mgl = '', $toc_mgr = '', $toc_mgt = '10', $toc_mgb = '', $toc_mgh = '', $toc_mgf = '', $toc_ohname = '', $toc_ehname = '', $toc_ofname = '', $toc_efname = '', $toc_ohvalue = 0, $toc_ehvalue = 0, $toc_ofvalue = 0, $toc_efvalue = 0, $toc_preHTML = '<h1 style="text-align:center;"> Sumário </h1>', $toc_postHTML = '', $toc_bookmarkText = '', $resetpagenum = '1', $pagenumstyle = '', $suppress = '', $orientation = '', $mgl = '', $mgr = '', $mgt = '', $mgb = '', $mgh = '', $mgf = '', $ohname = '', $ehname = '', $ofname = '', $efname = '', $ohvalue = 0, $ehvalue = 0, $ofvalue = 0, $efvalue = 0, $toc_id = 0, $pagesel = '', $toc_pagesel = '', $sheetsize = '', $toc_sheetsize = ''
        );
    }

    public static function corrigirCaminhoImagens($html) {
        $dom = new DOMDocument('', 'utf-8');

        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $links = $dom->getElementsByTagName('img');



        foreach ($links as $link) {
            $url = $link->getAttribute('src');
            $link->setAttribute('src', '../' . $url);
        }

        return $dom->saveHTML();
    }

    public static function gerarHtmlMPdf($category, &$mpdf) {

        $children = $category->getChildren();

        if (count($children)) {
            foreach ($children as $obJCategoryNode) {
                self::gerarHtml($obJCategoryNode, $mpdf);
                // pega o proximo nível
                $children2 = $obJCategoryNode->getChildren();
                if (count($children2)) {
                    foreach ($children2 as $obJCategoryNode2) {
                        self::gerarHtml($obJCategoryNode2, $mpdf);

                        // pega o proximo nível
                        $children3 = $obJCategoryNode2->getChildren();
                        if (count($children3)) {
                            foreach ($children3 as $obJCategoryNode3) {
                                self::gerarHtml($obJCategoryNode3, $mpdf);

                                // pega o proximo nível
                                $children4 = $obJCategoryNode3->getChildren();
                                if (count($children4)) {
                                    foreach ($children4 as $obJCategoryNode4) {
                                        // Busca os artigos da categoria
                                        self::gerarHtml($obJCategoryNode4, $mpdf);

                                        // pega o proximo nível
                                        $children5 = $obJCategoryNode4->getChildren();
                                        if (count($children5)) {
                                            foreach ($children5 as $obJCategoryNode5) {
                                                // Busca os artigos da categoria
                                                self::gerarHtml($obJCategoryNode5, $mpdf);
                                            } // foreach $children5
                                        } // if $children5
                                    } // foreach $children4
                                } // if $children4
                            } // foreach $children3
                        } // if $children3
                    } // foreach $children2
                } // if $children2
            } // foreach $children
        } // if $children
    }

    /**
     * Método para gerar html das categorias
     * @param type $obCategoria
     * @return $html
     */
    public static function gerarHtml($obCategoria, &$mpdf) {
        $mpdf->TOC_Entry($obCategoria->get('title'));
        $mpdf->WriteHTML("<h2 class='titulo-conteudo'>" . $obCategoria->get('title') . "</h2>");
        self::gerarHtmlArtigos($obCategoria, $mpdf);
    }

    public static function gerarHtmlArtigos($obCategoria, &$mpdf) {
        $arArtigos = self::recuperarArtigosPorCategorias($obCategoria->get('id'));
        $html = '';
        foreach ($arArtigos as $obArtigos) {
            if ($obArtigos->state == 1) {
                $mpdf->TOC_Entry($obArtigos->title, 2);
                $mpdf->WriteHTML("<h2 class='titulo-conteudo'>" . $obArtigos->title . "</h2>");

                if ($obArtigos->introtext) {
                    $mpdf->useSubstitutions = true;
                    $introtext = preg_replace("~<!-- pagebreak -->~", '<pagebreak></pagebreak>', $obArtigos->introtext);
                    $introtext = AtualizarpdfcosisControllerAtualizarpdfs::corrigirCaminhoImagens($introtext);
                    $mpdf->WriteHTML($introtext);
                }
                if ($obArtigos->fulltext) {
                    $fulltext = preg_replace("~<!-- pagebreak -->~", '<pagebreak></pagebreak>', $obArtigos->fulltext);
                    $fulltext = AtualizarpdfcosisControllerAtualizarpdfs::corrigirCaminhoImagens($fulltext);
                    $mpdf->WriteHTML($fulltext);
                }
            }
        }

        //return $html;
    }

    public static function cabecalhoMPdf(&$mpdf) {
        $mpdf->SetHTMLHeader('

            <table id="cabecalho-pdf" width="100%"><tr>

            <td width="20%"><span style="font-weight: bold; font-style: italic;"><img src="components/com_atualizarpdfcosis/assets/images/pdf/logo-tesouro-pdf.jpg" width="90px" /></span></td>

            <td class="titulo-cabecalho" width="80%" style="text-align: right;">' . JRequest::getVar('titulo') . '</td>

            </tr></table>
            <br />
        ');
    }

    // Funçao para montar o rodapé
    public static function rodapeMPdf(&$mpdf) {
        $mpdf->SetHTMLFooter('

            <table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 8pt; color: #000000; font-style: italic; border-top: 1px solid #000;"><tr>

            <td width="33%"><span style="font-style: italic;"></span></td>

            <td width="33%" align="center" style="font-style: italic;">{PAGENO}/{nbpg}</td>

            <td width="33%" style="text-align: right; ">Secretaria do Tesouro Nacional</td>

            </tr></table>

        ');
    }

    /**
     * Recupera os artigos pelo id da categoria passado por parametro
     * @param integer $idCategoria
     * @return array
     */
    public static function recuperarArtigosPorAlias($alias) {
        $arArtigos = array();
        $db = JFactory::getDbo();
        $queryConteudo = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__content'))
                ->where('alias = ' . $db->Quote($alias));
        $db->setQuery($queryConteudo);
        $arArtigos = $db->loadObjectList();

        if (count($arArtigos)) {
            return array_pop($arArtigos);
        } else {
            return false;
        }
    }

    /**
     * Recupera os artigos pelo id da categoria passado por parametro
     * @param integer $idCategoria
     * @return array
     */
    public static function recuperarArtigosPorCategorias($idCategoria) {
        $arArtigos = array();
        $db = JFactory::getDbo();
        $queryConteudo = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__content'))
                ->where('catid = ' . $db->Quote($idCategoria))
                ->order('title');
        $db->setQuery($queryConteudo);
        $arArtigos = $db->loadObjectList();
        return $arArtigos;
    }

}
