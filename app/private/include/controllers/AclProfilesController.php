<?php
class AclProfilesController extends CRUDController
{
    public function edit($id = null)
    {
        parent::edit($id);

        // Update current user ACL just in case it has been modified with this request
        if (
            Authentication::get('user', 'acl[acl_profile_id]') == $id
            && $_SERVER['REQUEST_METHOD'] == 'POST'
        ) {
            $this->updateUserAcl(true);
        }
    }
}
