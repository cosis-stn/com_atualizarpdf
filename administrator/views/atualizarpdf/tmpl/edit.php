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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'media/com_atualizarpdfcosis/css/form.css');
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function () {

        js('input:hidden.categoria').each(function () {
            var name = js(this).attr('name');
            if (name.indexOf('categoriahidden')) {
                js('#jform_categoria option[value="' + js(this).val() + '"]').attr('selected', true);
            }
        });
        js("#jform_categoria").trigger("liszt:updated");
    });

    Joomla.submitbutton = function (task) {
        if (task == 'atualizarpdf.cancel') {
            Joomla.submitform(task, document.getElementById('atualizarpdf-form'));
        } else {

            if (task != 'atualizarpdf.cancel' && document.formvalidator.isValid(document.id('atualizarpdf-form'))) {

                Joomla.submitform(task, document.getElementById('atualizarpdf-form'));
            } else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form
    action="<?php echo JRoute::_('index.php?option=com_atualizarpdfcosis&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" enctype="multipart/form-data" name="adminForm" id="atualizarpdf-form" class="form-validate">

    <div class="form-horizontal">
<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_ATUALIZARPDFCOSIS_TITLE_ATUALIZARPDF', true)); ?>
        <div class="row-fluid">
            <div class="span10 form-horizontal">
                <fieldset class="adminform">

                    <input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
                    <input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
                    <input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
                    <input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
                    <input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />

<?php if (empty($this->item->created_by)) { ?>
                        <input type="hidden" name="jform[created_by]" value="<?php echo JFactory::getUser()->id; ?>" />

<?php } else {
    ?>
                        <input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>" />

<?php } ?>
                    <?php if (empty($this->item->modified_by)) { ?>
                        <input type="hidden" name="jform[modified_by]" value="<?php echo JFactory::getUser()->id; ?>" />

<?php } else {
    ?>
                        <input type="hidden" name="jform[modified_by]" value="<?php echo $this->item->modified_by; ?>" />

<?php } ?>				<?php echo $this->form->renderField('grupo'); ?>
                    <?php echo $this->form->renderField('manual'); ?>
                    <?php echo $this->form->renderField('atualizar'); ?>
                    <?php echo $this->form->renderField('categoria'); ?>

                    <?php
                    foreach ((array) $this->item->categoria as $value):
                        if (!is_array($value)):
                            echo '<input type="hidden" class="categoria" name="jform[categoriahidden][' . $value . ']" value="' . $value . '" />';
                        endif;
                    endforeach;
                    ?>

                    <?php if ($this->state->params->get('save_history', 1)) : ?>
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('version_note'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('version_note'); ?></div>
                        </div>
<?php endif; ?>
                </fieldset>
            </div>
        </div>
<?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php if (JFactory::getUser()->authorise('core.admin', 'atualizarpdfcosis')) : ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL', true)); ?>
            <?php echo $this->form->getInput('rules'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php endif; ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="task" value=""/>
<?php echo JHtml::_('form.token'); ?>

    </div>
</form>
