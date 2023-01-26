<?php
class ParticipantsController extends CRUDController
{
    /**
     * Add custom actions for detail view
     *
     * @param array $element
     * @return array
     */
    protected function detailActions($element)
    {
        $actions = parent::detailActions($element);

        if ($element[$this->model_name]['is_winner']) {
            $actions['self-reset-tfa'] = [
                'active' => ACL\DbACL::canContentTypeAction($this->model_name, 'Update'),
                'url' => Router::url(['action' => 'resendWinnerEmail', $element['Participant']['id']]),
                'confirm' => __('Email will be resend. Continue?', true),
                'label' => __('Resend winner email', true),
            ];
        }

        return $actions;
    }

    public function resendWinnerEmail($id)
    {
        if (!ACL\DbACL::canContentTypeAction($this->model_name, 'Update')) $this->forbidden();

        /** @var Participant $Participant **/
        $Participant = MySQLModel::getInstance('Participant');
        $participant = $Participant->getById($id);

        if (!$participant || !$participant['Participant']['is_winner']) $this->forbidden();
        $Participant->sendEmailRef(1, $id);

        $this->set('participant', $participant);
    }
}
