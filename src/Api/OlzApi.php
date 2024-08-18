<?php

namespace Olz\Api;

use Olz\Apps\OlzApps;
use PhpTypeScriptApi\Api;

// Needed because this file can be called directly.
require_once __DIR__.'/../../vendor/autoload.php';

class OlzApi extends Api {
    public function __construct(
        Endpoints\OnDailyEndpoint $onDailyEndpoint,
        Endpoints\OnContinuouslyEndpoint $onContinuouslyEndpoint,
        Endpoints\LoginEndpoint $loginEndpoint,
        Endpoints\ResetPasswordEndpoint $resetPasswordEndpoint,
        Endpoints\SwitchUserEndpoint $switchUserEndpoint,
        Endpoints\LogoutEndpoint $logoutEndpoint,
        Endpoints\GetAuthenticatedUserEndpoint $getAuthenticatedUserEndpoint,
        Endpoints\GetAuthenticatedRolesEndpoint $getAuthenticatedRolesEndpoint,
        Endpoints\VerifyUserEmailEndpoint $verifyUserEmailEndpoint,
        Endpoints\UpdateUserPasswordEndpoint $updateUserPasswordEndpoint,
        Endpoints\SignUpWithPasswordEndpoint $signUpWithPasswordEndpoint,
        Endpoints\LoginWithStravaEndpoint $loginWithStravaEndpoint,
        Endpoints\SignUpWithStravaEndpoint $signUpWithStravaEndpoint,
        Endpoints\ExecuteEmailReactionEndpoint $executeEmailReactionEndpoint,
        Endpoints\LinkTelegramEndpoint $linkTelegramEndpoint,
        Endpoints\OnTelegramEndpoint $onTelegramEndpoint,
        Endpoints\StartUploadEndpoint $startUploadEndpoint,
        Endpoints\UpdateUploadEndpoint $updateUploadEndpoint,
        Endpoints\FinishUploadEndpoint $finishUploadEndpoint,
        Endpoints\SearchEntitiesEndpoint $searchEntitiesEndpoint,
        \Olz\Karten\Endpoints\CreateKarteEndpoint $createKarteEndpoint,
        \Olz\Karten\Endpoints\GetKarteEndpoint $getKarteEndpoint,
        \Olz\Karten\Endpoints\EditKarteEndpoint $editKarteEndpoint,
        \Olz\Karten\Endpoints\UpdateKarteEndpoint $updateKarteEndpoint,
        \Olz\Karten\Endpoints\DeleteKarteEndpoint $deleteKarteEndpoint,
        \Olz\News\Endpoints\CreateNewsEndpoint $createNewsEndpoint,
        \Olz\News\Endpoints\GetNewsEndpoint $getNewsEndpoint,
        \Olz\News\Endpoints\EditNewsEndpoint $editNewsEndpoint,
        \Olz\News\Endpoints\UpdateNewsEndpoint $updateNewsEndpoint,
        \Olz\News\Endpoints\DeleteNewsEndpoint $deleteNewsEndpoint,
        \Olz\Roles\Endpoints\CreateRoleEndpoint $createRoleEndpoint,
        \Olz\Roles\Endpoints\GetRoleEndpoint $getRoleEndpoint,
        \Olz\Roles\Endpoints\EditRoleEndpoint $editRoleEndpoint,
        \Olz\Roles\Endpoints\UpdateRoleEndpoint $updateRoleEndpoint,
        \Olz\Roles\Endpoints\DeleteRoleEndpoint $deleteRoleEndpoint,
        \Olz\Roles\Endpoints\AddUserRoleMembershipEndpoint $addUserRoleMembershipEndpoint,
        \Olz\Roles\Endpoints\RemoveUserRoleMembershipEndpoint $removeUserRoleMembershipEndpoint,
        \Olz\Snippets\Endpoints\GetSnippetEndpoint $getSnippetEndpoint,
        \Olz\Snippets\Endpoints\EditSnippetEndpoint $editSnippetEndpoint,
        \Olz\Snippets\Endpoints\UpdateSnippetEndpoint $updateSnippetEndpoint,
        \Olz\Service\Endpoints\CreateDownloadEndpoint $createDownloadEndpoint,
        \Olz\Service\Endpoints\GetDownloadEndpoint $getDownloadEndpoint,
        \Olz\Service\Endpoints\EditDownloadEndpoint $editDownloadEndpoint,
        \Olz\Service\Endpoints\UpdateDownloadEndpoint $updateDownloadEndpoint,
        \Olz\Service\Endpoints\DeleteDownloadEndpoint $deleteDownloadEndpoint,
        \Olz\Service\Endpoints\CreateLinkEndpoint $createLinkEndpoint,
        \Olz\Service\Endpoints\GetLinkEndpoint $getLinkEndpoint,
        \Olz\Service\Endpoints\EditLinkEndpoint $editLinkEndpoint,
        \Olz\Service\Endpoints\UpdateLinkEndpoint $updateLinkEndpoint,
        \Olz\Service\Endpoints\DeleteLinkEndpoint $deleteLinkEndpoint,
        \Olz\Startseite\Endpoints\CreateWeeklyPictureEndpoint $createWeeklyPictureEndpoint,
        \Olz\Startseite\Endpoints\GetWeeklyPictureEndpoint $getWeeklyPictureEndpoint,
        \Olz\Startseite\Endpoints\EditWeeklyPictureEndpoint $editWeeklyPictureEndpoint,
        \Olz\Startseite\Endpoints\UpdateWeeklyPictureEndpoint $updateWeeklyPictureEndpoint,
        \Olz\Startseite\Endpoints\DeleteWeeklyPictureEndpoint $deleteWeeklyPictureEndpoint,
        \Olz\Termine\Endpoints\CreateTerminEndpoint $createTerminEndpoint,
        \Olz\Termine\Endpoints\GetTerminEndpoint $getTerminEndpoint,
        \Olz\Termine\Endpoints\EditTerminEndpoint $editTerminEndpoint,
        \Olz\Termine\Endpoints\UpdateTerminEndpoint $updateTerminEndpoint,
        \Olz\Termine\Endpoints\DeleteTerminEndpoint $deleteTerminEndpoint,
        \Olz\Termine\Endpoints\CreateTerminLabelEndpoint $createTerminLabelEndpoint,
        \Olz\Termine\Endpoints\ListTerminLabelsEndpoint $listTerminLabelsEndpoint,
        \Olz\Termine\Endpoints\GetTerminLabelEndpoint $getTerminLabelEndpoint,
        \Olz\Termine\Endpoints\EditTerminLabelEndpoint $editTerminLabelEndpoint,
        \Olz\Termine\Endpoints\UpdateTerminLabelEndpoint $updateTerminLabelEndpoint,
        \Olz\Termine\Endpoints\DeleteTerminLabelEndpoint $deleteTerminLabelEndpoint,
        \Olz\Termine\Endpoints\CreateTerminLocationEndpoint $createTerminLocationEndpoint,
        \Olz\Termine\Endpoints\GetTerminLocationEndpoint $getTerminLocationEndpoint,
        \Olz\Termine\Endpoints\EditTerminLocationEndpoint $editTerminLocationEndpoint,
        \Olz\Termine\Endpoints\UpdateTerminLocationEndpoint $updateTerminLocationEndpoint,
        \Olz\Termine\Endpoints\DeleteTerminLocationEndpoint $deleteTerminLocationEndpoint,
        \Olz\Termine\Endpoints\CreateTerminTemplateEndpoint $createTerminTemplateEndpoint,
        \Olz\Termine\Endpoints\GetTerminTemplateEndpoint $getTerminTemplateEndpoint,
        \Olz\Termine\Endpoints\EditTerminTemplateEndpoint $editTerminTemplateEndpoint,
        \Olz\Termine\Endpoints\UpdateTerminTemplateEndpoint $updateTerminTemplateEndpoint,
        \Olz\Termine\Endpoints\DeleteTerminTemplateEndpoint $deleteTerminTemplateEndpoint,
        \Olz\Users\Endpoints\CreateUserEndpoint $createUserEndpoint,
        \Olz\Users\Endpoints\GetUserEndpoint $getUserEndpoint,
        \Olz\Users\Endpoints\EditUserEndpoint $editUserEndpoint,
        \Olz\Users\Endpoints\UpdateUserEndpoint $updateUserEndpoint,
        \Olz\Users\Endpoints\DeleteUserEndpoint $deleteUserEndpoint,
    ) {
        $this->registerEndpoint('onDaily', $onDailyEndpoint);
        $this->registerEndpoint('onContinuously', $onContinuouslyEndpoint);
        $this->registerEndpoint('login', $loginEndpoint);
        $this->registerEndpoint('resetPassword', $resetPasswordEndpoint);
        $this->registerEndpoint('switchUser', $switchUserEndpoint);
        $this->registerEndpoint('logout', $logoutEndpoint);
        $this->registerEndpoint('getAuthenticatedUser', $getAuthenticatedUserEndpoint);
        $this->registerEndpoint('getAuthenticatedRoles', $getAuthenticatedRolesEndpoint);
        $this->registerEndpoint('verifyUserEmail', $verifyUserEmailEndpoint);
        $this->registerEndpoint('updatePassword', $updateUserPasswordEndpoint);
        $this->registerEndpoint('signUpWithPassword', $signUpWithPasswordEndpoint);
        $this->registerEndpoint('loginWithStrava', $loginWithStravaEndpoint);
        $this->registerEndpoint('signUpWithStrava', $signUpWithStravaEndpoint);
        $this->registerEndpoint('executeEmailReaction', $executeEmailReactionEndpoint);
        $this->registerEndpoint('linkTelegram', $linkTelegramEndpoint);
        $this->registerEndpoint('onTelegram', $onTelegramEndpoint);
        $this->registerEndpoint('startUpload', $startUploadEndpoint);
        $this->registerEndpoint('updateUpload', $updateUploadEndpoint);
        $this->registerEndpoint('finishUpload', $finishUploadEndpoint);
        $this->registerEndpoint('searchEntities', $searchEntitiesEndpoint);

        // Downloads

        $this->registerEndpoint('createDownload', $createDownloadEndpoint);
        $this->registerEndpoint('getDownload', $getDownloadEndpoint);
        $this->registerEndpoint('editDownload', $editDownloadEndpoint);
        $this->registerEndpoint('updateDownload', $updateDownloadEndpoint);
        $this->registerEndpoint('deleteDownload', $deleteDownloadEndpoint);

        // Karten

        $this->registerEndpoint('createKarte', $createKarteEndpoint);
        $this->registerEndpoint('getKarte', $getKarteEndpoint);
        $this->registerEndpoint('editKarte', $editKarteEndpoint);
        $this->registerEndpoint('updateKarte', $updateKarteEndpoint);
        $this->registerEndpoint('deleteKarte', $deleteKarteEndpoint);

        // Links

        $this->registerEndpoint('createLink', $createLinkEndpoint);
        $this->registerEndpoint('getLink', $getLinkEndpoint);
        $this->registerEndpoint('editLink', $editLinkEndpoint);
        $this->registerEndpoint('updateLink', $updateLinkEndpoint);
        $this->registerEndpoint('deleteLink', $deleteLinkEndpoint);

        // News

        $this->registerEndpoint('createNews', $createNewsEndpoint);
        $this->registerEndpoint('getNews', $getNewsEndpoint);
        $this->registerEndpoint('editNews', $editNewsEndpoint);
        $this->registerEndpoint('updateNews', $updateNewsEndpoint);
        $this->registerEndpoint('deleteNews', $deleteNewsEndpoint);

        // Roles

        $this->registerEndpoint('createRole', $createRoleEndpoint);
        $this->registerEndpoint('getRole', $getRoleEndpoint);
        $this->registerEndpoint('editRole', $editRoleEndpoint);
        $this->registerEndpoint('updateRole', $updateRoleEndpoint);
        $this->registerEndpoint('deleteRole', $deleteRoleEndpoint);

        $this->registerEndpoint('addUserRoleMembership', $addUserRoleMembershipEndpoint);
        $this->registerEndpoint('removeUserRoleMembership', $removeUserRoleMembershipEndpoint);

        // Snippets

        $this->registerEndpoint('getSnippet', $getSnippetEndpoint);
        $this->registerEndpoint('editSnippet', $editSnippetEndpoint);
        $this->registerEndpoint('updateSnippet', $updateSnippetEndpoint);

        // Startseite

        $this->registerEndpoint('createWeeklyPicture', $createWeeklyPictureEndpoint);
        $this->registerEndpoint('getWeeklyPicture', $getWeeklyPictureEndpoint);
        $this->registerEndpoint('editWeeklyPicture', $editWeeklyPictureEndpoint);
        $this->registerEndpoint('updateWeeklyPicture', $updateWeeklyPictureEndpoint);
        $this->registerEndpoint('deleteWeeklyPicture', $deleteWeeklyPictureEndpoint);

        // Termine

        $this->registerEndpoint('createTermin', $createTerminEndpoint);
        $this->registerEndpoint('getTermin', $getTerminEndpoint);
        $this->registerEndpoint('editTermin', $editTerminEndpoint);
        $this->registerEndpoint('updateTermin', $updateTerminEndpoint);
        $this->registerEndpoint('deleteTermin', $deleteTerminEndpoint);

        // Termin Label

        $this->registerEndpoint('createTerminLabel', $createTerminLabelEndpoint);
        $this->registerEndpoint('listTerminLabels', $listTerminLabelsEndpoint);
        $this->registerEndpoint('getTerminLabel', $getTerminLabelEndpoint);
        $this->registerEndpoint('editTerminLabel', $editTerminLabelEndpoint);
        $this->registerEndpoint('updateTerminLabel', $updateTerminLabelEndpoint);
        $this->registerEndpoint('deleteTerminLabel', $deleteTerminLabelEndpoint);

        // Termin Locations

        $this->registerEndpoint('createTerminLocation', $createTerminLocationEndpoint);
        $this->registerEndpoint('getTerminLocation', $getTerminLocationEndpoint);
        $this->registerEndpoint('editTerminLocation', $editTerminLocationEndpoint);
        $this->registerEndpoint('updateTerminLocation', $updateTerminLocationEndpoint);
        $this->registerEndpoint('deleteTerminLocation', $deleteTerminLocationEndpoint);

        // Termin Templates

        $this->registerEndpoint('createTerminTemplate', $createTerminTemplateEndpoint);
        $this->registerEndpoint('getTerminTemplate', $getTerminTemplateEndpoint);
        $this->registerEndpoint('editTerminTemplate', $editTerminTemplateEndpoint);
        $this->registerEndpoint('updateTerminTemplate', $updateTerminTemplateEndpoint);
        $this->registerEndpoint('deleteTerminTemplate', $deleteTerminTemplateEndpoint);

        // Users

        $this->registerEndpoint('createUser', $createUserEndpoint);
        $this->registerEndpoint('getUser', $getUserEndpoint);
        $this->registerEndpoint('editUser', $editUserEndpoint);
        $this->registerEndpoint('updateUser', $updateUserEndpoint);
        $this->registerEndpoint('deleteUser', $deleteUserEndpoint);

        OlzApps::registerAllEndpoints($this);
    }

    public static function generate(): void {
        $olz_api = self::getShallowInstance();

        file_put_contents(
            __DIR__.'/client/generated_olz_api_types.ts',
            $olz_api->getTypeScriptDefinition('OlzApi')
        );

        echo "\nOLZ API client generated.\n";
    }

    public static function getShallowInstance(): self {
        return new self(
            new Endpoints\OnDailyEndpoint(),
            new Endpoints\OnContinuouslyEndpoint(),
            new Endpoints\LoginEndpoint(),
            new Endpoints\ResetPasswordEndpoint(),
            new Endpoints\SwitchUserEndpoint(),
            new Endpoints\LogoutEndpoint(),
            new Endpoints\GetAuthenticatedUserEndpoint(),
            new Endpoints\GetAuthenticatedRolesEndpoint(),
            new Endpoints\VerifyUserEmailEndpoint(),
            new Endpoints\UpdateUserPasswordEndpoint(),
            new Endpoints\SignUpWithPasswordEndpoint(),
            new Endpoints\LoginWithStravaEndpoint(),
            new Endpoints\SignUpWithStravaEndpoint(),
            new Endpoints\ExecuteEmailReactionEndpoint(),
            new Endpoints\LinkTelegramEndpoint(),
            new Endpoints\OnTelegramEndpoint(),
            new Endpoints\StartUploadEndpoint(),
            new Endpoints\UpdateUploadEndpoint(),
            new Endpoints\FinishUploadEndpoint(),
            new Endpoints\SearchEntitiesEndpoint(),
            new \Olz\Karten\Endpoints\CreateKarteEndpoint(),
            new \Olz\Karten\Endpoints\GetKarteEndpoint(),
            new \Olz\Karten\Endpoints\EditKarteEndpoint(),
            new \Olz\Karten\Endpoints\UpdateKarteEndpoint(),
            new \Olz\Karten\Endpoints\DeleteKarteEndpoint(),
            new \Olz\News\Endpoints\CreateNewsEndpoint(),
            new \Olz\News\Endpoints\GetNewsEndpoint(),
            new \Olz\News\Endpoints\EditNewsEndpoint(),
            new \Olz\News\Endpoints\UpdateNewsEndpoint(),
            new \Olz\News\Endpoints\DeleteNewsEndpoint(),
            new \Olz\Roles\Endpoints\CreateRoleEndpoint(),
            new \Olz\Roles\Endpoints\GetRoleEndpoint(),
            new \Olz\Roles\Endpoints\EditRoleEndpoint(),
            new \Olz\Roles\Endpoints\UpdateRoleEndpoint(),
            new \Olz\Roles\Endpoints\DeleteRoleEndpoint(),
            new \Olz\Roles\Endpoints\AddUserRoleMembershipEndpoint(),
            new \Olz\Roles\Endpoints\RemoveUserRoleMembershipEndpoint(),
            new \Olz\Snippets\Endpoints\GetSnippetEndpoint(),
            new \Olz\Snippets\Endpoints\EditSnippetEndpoint(),
            new \Olz\Snippets\Endpoints\UpdateSnippetEndpoint(),
            new \Olz\Service\Endpoints\CreateDownloadEndpoint(),
            new \Olz\Service\Endpoints\GetDownloadEndpoint(),
            new \Olz\Service\Endpoints\EditDownloadEndpoint(),
            new \Olz\Service\Endpoints\UpdateDownloadEndpoint(),
            new \Olz\Service\Endpoints\DeleteDownloadEndpoint(),
            new \Olz\Service\Endpoints\CreateLinkEndpoint(),
            new \Olz\Service\Endpoints\GetLinkEndpoint(),
            new \Olz\Service\Endpoints\EditLinkEndpoint(),
            new \Olz\Service\Endpoints\UpdateLinkEndpoint(),
            new \Olz\Service\Endpoints\DeleteLinkEndpoint(),
            new \Olz\Startseite\Endpoints\CreateWeeklyPictureEndpoint(),
            new \Olz\Startseite\Endpoints\GetWeeklyPictureEndpoint(),
            new \Olz\Startseite\Endpoints\EditWeeklyPictureEndpoint(),
            new \Olz\Startseite\Endpoints\UpdateWeeklyPictureEndpoint(),
            new \Olz\Startseite\Endpoints\DeleteWeeklyPictureEndpoint(),
            new \Olz\Termine\Endpoints\CreateTerminEndpoint(),
            new \Olz\Termine\Endpoints\GetTerminEndpoint(),
            new \Olz\Termine\Endpoints\EditTerminEndpoint(),
            new \Olz\Termine\Endpoints\UpdateTerminEndpoint(),
            new \Olz\Termine\Endpoints\DeleteTerminEndpoint(),
            new \Olz\Termine\Endpoints\CreateTerminLabelEndpoint(),
            new \Olz\Termine\Endpoints\ListTerminLabelsEndpoint(),
            new \Olz\Termine\Endpoints\GetTerminLabelEndpoint(),
            new \Olz\Termine\Endpoints\EditTerminLabelEndpoint(),
            new \Olz\Termine\Endpoints\UpdateTerminLabelEndpoint(),
            new \Olz\Termine\Endpoints\DeleteTerminLabelEndpoint(),
            new \Olz\Termine\Endpoints\CreateTerminLocationEndpoint(),
            new \Olz\Termine\Endpoints\GetTerminLocationEndpoint(),
            new \Olz\Termine\Endpoints\EditTerminLocationEndpoint(),
            new \Olz\Termine\Endpoints\UpdateTerminLocationEndpoint(),
            new \Olz\Termine\Endpoints\DeleteTerminLocationEndpoint(),
            new \Olz\Termine\Endpoints\CreateTerminTemplateEndpoint(),
            new \Olz\Termine\Endpoints\GetTerminTemplateEndpoint(),
            new \Olz\Termine\Endpoints\EditTerminTemplateEndpoint(),
            new \Olz\Termine\Endpoints\UpdateTerminTemplateEndpoint(),
            new \Olz\Termine\Endpoints\DeleteTerminTemplateEndpoint(),
            new \Olz\Users\Endpoints\CreateUserEndpoint(),
            new \Olz\Users\Endpoints\GetUserEndpoint(),
            new \Olz\Users\Endpoints\EditUserEndpoint(),
            new \Olz\Users\Endpoints\UpdateUserEndpoint(),
            new \Olz\Users\Endpoints\DeleteUserEndpoint(),
        );
    }
}

// @codeCoverageIgnoreStart
// Reason: Hard to test.
if (isset($_SERVER['argv']) && basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    OlzApi::generate();
}
// @codeCoverageIgnoreEnd
