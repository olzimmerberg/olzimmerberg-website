/** ### This file is auto-generated, modifying is futile! ### */

export type OlzAuthenticatedUser = {
    'id': number,
    'firstName': string,
    'lastName': string,
    'username': string,
}|null;

export type OlzAuthenticatedRole = {
    'id': number,
    'name': string,
    'username': string,
};

export type OlzSearchableEntityTypes = 'SolvEvent'|'TerminLocation'|'TerminTemplate'|'Role'|'User';

export type OlzEntityResult = {
    'id': number,
    'title': string,
};

export type OlzMetaData = {
    'ownerUserId': number|null,
    'ownerRoleId': number|null,
    'onOff': boolean,
};

export type OlzDownloadData = {
    'name': string,
    'position': number|null,
    'fileId': string|null,
};

export type OlzMetaDataOrNull = {
    'ownerUserId': number|null,
    'ownerRoleId': number|null,
    'onOff': boolean,
}|null;

export type OlzDownloadDataOrNull = {
    'name': string,
    'position': number|null,
    'fileId': string|null,
}|null;

export type OlzKarteData = {
    'kartennr': number|null,
    'name': string,
    'latitude': number|null,
    'longitude': number|null,
    'year': number|null,
    'scale': string|null,
    'place': string|null,
    'zoom': number|null,
    'kind': OlzKarteKind,
    'previewImageId': string|null,
};

export type OlzKarteKind = 'ol'|'stadt'|'scool'|null;

export type OlzKarteDataOrNull = {
    'kartennr': number|null,
    'name': string,
    'latitude': number|null,
    'longitude': number|null,
    'year': number|null,
    'scale': string|null,
    'place': string|null,
    'zoom': number|null,
    'kind': OlzKarteKind,
    'previewImageId': string|null,
}|null;

export type OlzLinkData = {
    'position': number|null,
    'name': string,
    'url': string,
};

export type OlzLinkDataOrNull = {
    'position': number|null,
    'name': string,
    'url': string,
}|null;

export type OlzNewsData = {
    'format': OlzNewsFormat,
    'authorUserId': number|null,
    'authorRoleId': number|null,
    'authorName': string|null,
    'authorEmail': string|null,
    'publishAt': string|null,
    'title': string,
    'teaser': string,
    'content': string,
    'externalUrl': string|null,
    'tags': Array<string>,
    'terminId': number|null,
    'imageIds': Array<string>|null,
    'fileIds': Array<string>,
};

export type OlzNewsFormat = 'aktuell'|'kaderblog'|'forum'|'galerie'|'video'|'anonymous';

export type OlzNewsDataOrNull = {
    'format': OlzNewsFormat,
    'authorUserId': number|null,
    'authorRoleId': number|null,
    'authorName': string|null,
    'authorEmail': string|null,
    'publishAt': string|null,
    'title': string,
    'teaser': string,
    'content': string,
    'externalUrl': string|null,
    'tags': Array<string>,
    'terminId': number|null,
    'imageIds': Array<string>|null,
    'fileIds': Array<string>,
}|null;

export type OlzRoleData = {
    'firstName': string,
    'lastName': string,
    'username': string,
    'password': string|null,
    'email': string|null,
    'phone': string|null,
    'gender': 'M'|'F'|'O'|null,
    'birthdate': string|null,
    'street': string|null,
    'postalCode': string|null,
    'city': string|null,
    'region': string|null,
    'countryCode': string|null,
    'siCardNumber': number|null,
    'solvNumber': string|null,
    'avatarId': string|null,
};

export type OlzRoleDataOrNull = {
    'firstName': string,
    'lastName': string,
    'username': string,
    'password': string|null,
    'email': string|null,
    'phone': string|null,
    'gender': 'M'|'F'|'O'|null,
    'birthdate': string|null,
    'street': string|null,
    'postalCode': string|null,
    'city': string|null,
    'region': string|null,
    'countryCode': string|null,
    'siCardNumber': number|null,
    'solvNumber': string|null,
    'avatarId': string|null,
}|null;

export type OlzSnippetData = {
    'text': string,
    'imageIds': Array<string>,
    'fileIds': Array<string>,
};

export type OlzSnippetDataOrNull = {
    'text': string,
    'imageIds': Array<string>,
    'fileIds': Array<string>,
}|null;

export type OlzWeeklyPictureData = {
    'text': string,
    'imageId': string,
    'publishedDate': string|null,
};

export type OlzWeeklyPictureDataOrNull = {
    'text': string,
    'imageId': string,
    'publishedDate': string|null,
}|null;

export type OlzTerminData = {
    'startDate': string,
    'startTime': string|null,
    'endDate': string|null,
    'endTime': string|null,
    'title': string,
    'text': string,
    'deadline': string|null,
    'newsletter': boolean,
    'solvId': number|null,
    'go2olId': string|null,
    'types': Array<string>,
    'locationId': number|null,
    'coordinateX': number|null,
    'coordinateY': number|null,
    'imageIds': Array<string>,
    'fileIds': Array<string>,
};

export type OlzTerminDataOrNull = {
    'startDate': string,
    'startTime': string|null,
    'endDate': string|null,
    'endTime': string|null,
    'title': string,
    'text': string,
    'deadline': string|null,
    'newsletter': boolean,
    'solvId': number|null,
    'go2olId': string|null,
    'types': Array<string>,
    'locationId': number|null,
    'coordinateX': number|null,
    'coordinateY': number|null,
    'imageIds': Array<string>,
    'fileIds': Array<string>,
}|null;

export type OlzTerminLabelData = {
    'ident': string,
    'name': string,
    'details': string,
    'icon': string|null,
    'position': number|null,
    'imageIds': Array<string>,
    'fileIds': Array<string>,
};

export type OlzTerminLabelDataOrNull = {
    'ident': string,
    'name': string,
    'details': string,
    'icon': string|null,
    'position': number|null,
    'imageIds': Array<string>,
    'fileIds': Array<string>,
}|null;

export type OlzTerminLocationData = {
    'name': string,
    'details': string,
    'latitude': number,
    'longitude': number,
    'imageIds': Array<string>,
};

export type OlzTerminLocationDataOrNull = {
    'name': string,
    'details': string,
    'latitude': number,
    'longitude': number,
    'imageIds': Array<string>,
}|null;

export type OlzTerminTemplateData = {
    'startTime': string|null,
    'durationSeconds': number|null,
    'title': string,
    'text': string,
    'deadlineEarlierSeconds': number|null,
    'deadlineTime': string|null,
    'newsletter': boolean,
    'types': Array<string>,
    'locationId': number|null,
    'imageIds': Array<string>,
    'fileIds': Array<string>,
};

export type OlzTerminTemplateDataOrNull = {
    'startTime': string|null,
    'durationSeconds': number|null,
    'title': string,
    'text': string,
    'deadlineEarlierSeconds': number|null,
    'deadlineTime': string|null,
    'newsletter': boolean,
    'types': Array<string>,
    'locationId': number|null,
    'imageIds': Array<string>,
    'fileIds': Array<string>,
}|null;

export type OlzBookingData = {
    'registrationId': string,
    'values': {[key: string]: unknown},
};

export type OlzRegistrationData = {
    'title': string,
    'description': string,
    'infos': Array<OlzRegistrationInfo>,
    'opensAt': string|null,
    'closesAt': string|null,
};

export type OlzRegistrationInfo = {
    'type': 'email'|'firstName'|'lastName'|'gender'|'street'|'postalCode'|'city'|'region'|'countryCode'|'birthdate'|'phone'|'siCardNumber'|'solvNumber'|'string'|'enum'|'reservation',
    'isOptional': boolean,
    'title': string,
    'description': string,
    'options': {
    'text': Array<string>,
}|null,
};

export type OlzLogsQuery = {
    'channel': string,
    'targetDate': string|null,
    'firstDate': string|null,
    'lastDate': string|null,
    'minLogLevel': OlzLogLevel,
    'textSearch': string|null,
    'pageToken': string|null,
};

export type OlzLogLevel = 'debug'|'info'|'notice'|'warning'|'error'|'critical'|'alert'|'emergency'|null;

export type OlzTransportSuggestion = {
    'mainConnection': OlzTransportConnection,
    'sideConnections': Array<{
    'connection': OlzTransportConnection,
    'joiningStationId': string,
}>,
    'originInfo': Array<OlzOriginInfo>,
    'debug': string,
};

export type OlzTransportConnection = {
    'sections': Array<OlzTransportSection>,
};

export type OlzTransportSection = {
    'departure': OlzTransportHalt,
    'arrival': OlzTransportHalt,
    'passList': Array<OlzTransportHalt>,
    'isWalk': boolean,
};

export type OlzTransportHalt = {
    'stationId': string,
    'stationName': string,
    'time': string,
};

export type OlzOriginInfo = {
    'halt': OlzTransportHalt,
    'isSkipped': boolean,
    'rating': number,
};

export type OlzPanini2024PictureData = {
    'id': number|null,
    'line1': string,
    'line2': string,
    'residence': string,
    'uploadId': string,
    'onOff': boolean,
    'info1': string,
    'info2': string,
    'info3': string,
    'info4': string,
    'info5': string,
};

// eslint-disable-next-line no-shadow
export type OlzApiEndpoint =
    'onDaily'|
    'onContinuously'|
    'login'|
    'resetPassword'|
    'switchUser'|
    'logout'|
    'getAuthenticatedUser'|
    'getAuthenticatedRoles'|
    'verifyUserEmail'|
    'updatePassword'|
    'signUpWithPassword'|
    'loginWithStrava'|
    'signUpWithStrava'|
    'executeEmailReaction'|
    'linkTelegram'|
    'onTelegram'|
    'startUpload'|
    'updateUpload'|
    'finishUpload'|
    'searchEntities'|
    'createDownload'|
    'getDownload'|
    'editDownload'|
    'updateDownload'|
    'deleteDownload'|
    'createKarte'|
    'getKarte'|
    'editKarte'|
    'updateKarte'|
    'deleteKarte'|
    'createLink'|
    'getLink'|
    'editLink'|
    'updateLink'|
    'deleteLink'|
    'createNews'|
    'getNews'|
    'editNews'|
    'updateNews'|
    'deleteNews'|
    'createRole'|
    'getRole'|
    'editRole'|
    'updateRole'|
    'deleteRole'|
    'addUserRoleMembership'|
    'removeUserRoleMembership'|
    'getSnippet'|
    'editSnippet'|
    'updateSnippet'|
    'createWeeklyPicture'|
    'getWeeklyPicture'|
    'editWeeklyPicture'|
    'updateWeeklyPicture'|
    'deleteWeeklyPicture'|
    'createTermin'|
    'getTermin'|
    'editTermin'|
    'updateTermin'|
    'deleteTermin'|
    'createTerminLabel'|
    'listTerminLabels'|
    'getTerminLabel'|
    'editTerminLabel'|
    'updateTerminLabel'|
    'deleteTerminLabel'|
    'createTerminLocation'|
    'getTerminLocation'|
    'editTerminLocation'|
    'updateTerminLocation'|
    'deleteTerminLocation'|
    'createTerminTemplate'|
    'getTerminTemplate'|
    'editTerminTemplate'|
    'updateTerminTemplate'|
    'deleteTerminTemplate'|
    'createUser'|
    'getUser'|
    'editUser'|
    'updateUser'|
    'deleteUser'|
    'createBooking'|
    'createRegistration'|
    'getManagedUsers'|
    'getPrefillValues'|
    'getRegistration'|
    'executeCommand'|
    'getWebdavAccessToken'|
    'revokeWebdavAccessToken'|
    'importTermine'|
    'getLogs'|
    'getAppMonitoringCredentials'|
    'updateNotificationSubscriptions'|
    'searchTransportConnection'|
    'listPanini2024Pictures'|
    'updateMyPanini2024'|
    'getMySkillLevels'|
    'updateMySkillLevels'|
    'registerSkillCategories'|
    'registerSkills'|
    'updateResults'|
    'getAppSearchEnginesCredentials'|
    'getAppStatisticsCredentials'|
    'getAppYoutubeCredentials';

type OlzApiEndpointMapping = {[key in OlzApiEndpoint]: unknown};

export interface OlzApiRequests extends OlzApiEndpointMapping {
    onDaily: {
            'authenticityCode': string,
        },
    onContinuously: {
            'authenticityCode': string,
        },
    login: {
            'usernameOrEmail': string,
            'password': string,
            'rememberMe': boolean,
        },
    resetPassword: {
            'usernameOrEmail': string,
            'recaptchaToken': string,
        },
    switchUser: {
            'userId': number,
        },
    logout: Record<string, never>|null,
    getAuthenticatedUser: Record<string, never>|null,
    getAuthenticatedRoles: Record<string, never>|null,
    verifyUserEmail: {
            'recaptchaToken': string,
        },
    updatePassword: {
            'id': number,
            'oldPassword': string,
            'newPassword': string,
        },
    signUpWithPassword: {
            'firstName': string,
            'lastName': string,
            'username': string,
            'password': string|null,
            'email': string|null,
            'phone': string|null,
            'gender': 'M'|'F'|'O'|null,
            'birthdate': string|null,
            'street': string|null,
            'postalCode': string|null,
            'city': string|null,
            'region': string|null,
            'countryCode': string|null,
            'siCardNumber': number|null,
            'solvNumber': string|null,
            'recaptchaToken': string,
        },
    loginWithStrava: {
            'code': string,
        },
    signUpWithStrava: {
            'stravaUser': string,
            'accessToken': string,
            'refreshToken': string,
            'expiresAt': string,
            'firstName': string,
            'lastName': string,
            'username': string,
            'email': string,
            'phone': string|null,
            'gender': 'M'|'F'|'O'|null,
            'birthdate': string|null,
            'street': string,
            'postalCode': string,
            'city': string,
            'region': string,
            'countryCode': string,
            'siCardNumber': number|null,
            'solvNumber': string|null,
        },
    executeEmailReaction: {
            'token': string,
        },
    linkTelegram: Record<string, never>|null,
    onTelegram: {
            'authenticityCode': string,
            'telegramEvent': string,
        },
    startUpload: {
            'suffix': string|null,
        },
    updateUpload: {
            'id': string,
            'part': number,
            'content': string,
        },
    finishUpload: {
            'id': string,
            'numberOfParts': number,
        },
    searchEntities: {
            'entityType': OlzSearchableEntityTypes,
            'query': string|null,
            'id': number|null,
        },
    createDownload: {
            'meta': OlzMetaData,
            'data': OlzDownloadData,
        },
    getDownload: {
            'id': number,
        },
    editDownload: {
            'id': number,
        },
    updateDownload: {
            'id': number,
            'meta': OlzMetaDataOrNull,
            'data': OlzDownloadDataOrNull,
        },
    deleteDownload: {
            'id': number,
        },
    createKarte: {
            'meta': OlzMetaData,
            'data': OlzKarteData,
        },
    getKarte: {
            'id': number,
        },
    editKarte: {
            'id': number,
        },
    updateKarte: {
            'id': number,
            'meta': OlzMetaDataOrNull,
            'data': OlzKarteDataOrNull,
        },
    deleteKarte: {
            'id': number,
        },
    createLink: {
            'meta': OlzMetaData,
            'data': OlzLinkData,
        },
    getLink: {
            'id': number,
        },
    editLink: {
            'id': number,
        },
    updateLink: {
            'id': number,
            'meta': OlzMetaDataOrNull,
            'data': OlzLinkDataOrNull,
        },
    deleteLink: {
            'id': number,
        },
    createNews: {
            'custom': {
            'recaptchaToken': string|null,
        },
            'meta': OlzMetaData,
            'data': OlzNewsData,
        },
    getNews: {
            'id': number,
        },
    editNews: {
            'id': number,
        },
    updateNews: {
            'id': number,
            'meta': OlzMetaDataOrNull,
            'data': OlzNewsDataOrNull,
        },
    deleteNews: {
            'id': number,
        },
    createRole: {
            'meta': OlzMetaData,
            'data': OlzRoleData,
        },
    getRole: {
            'id': number,
        },
    editRole: {
            'id': number,
        },
    updateRole: {
            'id': number,
            'meta': OlzMetaDataOrNull,
            'data': OlzRoleDataOrNull,
        },
    deleteRole: {
            'id': number,
        },
    addUserRoleMembership: {
            'ids': {
            'roleId': number,
            'userId': number,
        },
        },
    removeUserRoleMembership: {
            'ids': {
            'roleId': number,
            'userId': number,
        },
        },
    getSnippet: {
            'id': number,
        },
    editSnippet: {
            'id': number,
        },
    updateSnippet: {
            'id': number,
            'meta': OlzMetaDataOrNull,
            'data': OlzSnippetDataOrNull,
        },
    createWeeklyPicture: {
            'meta': OlzMetaData,
            'data': OlzWeeklyPictureData,
        },
    getWeeklyPicture: {
            'id': number,
        },
    editWeeklyPicture: {
            'id': number,
        },
    updateWeeklyPicture: {
            'id': number,
            'meta': OlzMetaDataOrNull,
            'data': OlzWeeklyPictureDataOrNull,
        },
    deleteWeeklyPicture: {
            'id': number,
        },
    createTermin: {
            'meta': OlzMetaData,
            'data': OlzTerminData,
        },
    getTermin: {
            'id': number,
        },
    editTermin: {
            'id': number,
        },
    updateTermin: {
            'id': number,
            'meta': OlzMetaDataOrNull,
            'data': OlzTerminDataOrNull,
        },
    deleteTermin: {
            'id': number,
        },
    createTerminLabel: {
            'meta': OlzMetaData,
            'data': OlzTerminLabelData,
        },
    listTerminLabels: Record<string, never>,
    getTerminLabel: {
            'id': number,
        },
    editTerminLabel: {
            'id': number,
        },
    updateTerminLabel: {
            'id': number,
            'meta': OlzMetaDataOrNull,
            'data': OlzTerminLabelDataOrNull,
        },
    deleteTerminLabel: {
            'id': number,
        },
    createTerminLocation: {
            'meta': OlzMetaData,
            'data': OlzTerminLocationData,
        },
    getTerminLocation: {
            'id': number,
        },
    editTerminLocation: {
            'id': number,
        },
    updateTerminLocation: {
            'id': number,
            'meta': OlzMetaDataOrNull,
            'data': OlzTerminLocationDataOrNull,
        },
    deleteTerminLocation: {
            'id': number,
        },
    createTerminTemplate: {
            'meta': OlzMetaData,
            'data': OlzTerminTemplateData,
        },
    getTerminTemplate: {
            'id': number,
        },
    editTerminTemplate: {
            'id': number,
        },
    updateTerminTemplate: {
            'id': number,
            'meta': OlzMetaDataOrNull,
            'data': OlzTerminTemplateDataOrNull,
        },
    deleteTerminTemplate: {
            'id': number,
        },
    createUser: {
            'custom': {
            'recaptchaToken': string|null,
        },
            'meta': OlzMetaData,
            'data': OlzRoleData,
        },
    getUser: {
            'id': number,
        },
    editUser: {
            'id': number,
        },
    updateUser: {
            'id': number,
            'meta': OlzMetaDataOrNull,
            'data': OlzRoleDataOrNull,
        },
    deleteUser: {
            'id': number,
        },
    createBooking: {
            'meta': OlzMetaData,
            'data': OlzBookingData,
        },
    createRegistration: {
            'meta': OlzMetaData,
            'data': OlzRegistrationData,
        },
    getManagedUsers: Record<string, never>|null,
    getPrefillValues: {
            'userId': number|null,
        },
    getRegistration: {
            'id': string,
        },
    executeCommand: {
            'command': string,
            'argv': string|null,
        },
    getWebdavAccessToken: Record<string, never>|null,
    revokeWebdavAccessToken: Record<string, never>|null,
    importTermine: Record<string, never>,
    getLogs: {
            'query': OlzLogsQuery,
        },
    getAppMonitoringCredentials: Record<string, never>,
    updateNotificationSubscriptions: {
            'deliveryType': 'email'|'telegram',
            'monthlyPreview': boolean,
            'weeklyPreview': boolean,
            'deadlineWarning': boolean,
            'deadlineWarningDays': '1'|'2'|'3'|'7',
            'dailySummary': boolean,
            'dailySummaryAktuell': boolean,
            'dailySummaryBlog': boolean,
            'dailySummaryForum': boolean,
            'dailySummaryGalerie': boolean,
            'dailySummaryTermine': boolean,
            'weeklySummary': boolean,
            'weeklySummaryAktuell': boolean,
            'weeklySummaryBlog': boolean,
            'weeklySummaryForum': boolean,
            'weeklySummaryGalerie': boolean,
            'weeklySummaryTermine': boolean,
        },
    searchTransportConnection: {
            'destination': string,
            'arrival': string,
        },
    listPanini2024Pictures: {
            'filter': {
            'idIs': number,
        }|{
            'page': number,
        }|null,
        },
    updateMyPanini2024: {
            'data': OlzPanini2024PictureData,
        },
    getMySkillLevels: {
            'skillFilter': {
            'categoryIdIn': Array<string>,
        }|null,
        },
    updateMySkillLevels: {
            'updates': {[key: string]: {
            'change': number,
        }},
        },
    registerSkillCategories: {
            'skillCategories': Array<{
            'name': string,
            'parentCategoryName': string|null,
        }>,
        },
    registerSkills: {
            'skills': Array<{
            'name': string,
            'categoryIds': Array<string>,
        }>,
        },
    updateResults: {
            'file': string,
            'content': string|null,
            'iofXmlFileId': string|null,
        },
    getAppSearchEnginesCredentials: Record<string, never>,
    getAppStatisticsCredentials: Record<string, never>,
    getAppYoutubeCredentials: Record<string, never>,
}

export interface OlzApiResponses extends OlzApiEndpointMapping {
    onDaily: Record<string, never>|null,
    onContinuously: Record<string, never>|null,
    login: {
            'status': 'INVALID_CREDENTIALS'|'BLOCKED'|'AUTHENTICATED',
            'numRemainingAttempts': number|null,
        },
    resetPassword: {
            'status': 'DENIED'|'ERROR'|'OK',
        },
    switchUser: {
            'status': 'OK',
        },
    logout: {
            'status': 'NO_SESSION'|'SESSION_CLOSED',
        },
    getAuthenticatedUser: {
            'user': OlzAuthenticatedUser,
        },
    getAuthenticatedRoles: {
            'roles': Array<OlzAuthenticatedRole>|null,
        },
    verifyUserEmail: {
            'status': 'OK'|'DENIED'|'ERROR',
        },
    updatePassword: {
            'status': 'OK'|'OTHER_USER'|'INVALID_OLD',
        },
    signUpWithPassword: {
            'status': 'OK'|'OK_NO_EMAIL_VERIFICATION'|'DENIED',
        },
    loginWithStrava: {
            'status': 'NOT_REGISTERED'|'INVALID_CODE'|'AUTHENTICATED',
            'tokenType': string|null,
            'expiresAt': string|null,
            'refreshToken': string|null,
            'accessToken': string|null,
            'userIdentifier': string|null,
            'firstName': string|null,
            'lastName': string|null,
            'gender': 'M'|'F'|'O'|null,
            'city': string|null,
            'region': string|null,
            'country': string|null,
            'profilePictureUrl': string|null,
        },
    signUpWithStrava: {
            'status': 'OK',
        },
    executeEmailReaction: {
            'status': 'INVALID_TOKEN'|'OK',
        },
    linkTelegram: {
            'botName': string,
            'pin': string,
        },
    onTelegram: Record<string, never>|null,
    startUpload: {
            'status': 'OK'|'ERROR',
            'id': string|null,
        },
    updateUpload: {
            'status': 'OK'|'ERROR',
        },
    finishUpload: {
            'status': 'OK'|'ERROR',
        },
    searchEntities: {
            'result': Array<OlzEntityResult>,
        },
    createDownload: {
            'status': 'OK'|'ERROR',
            'id': number|null,
        },
    getDownload: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzDownloadData,
        },
    editDownload: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzDownloadData,
        },
    updateDownload: {
            'status': 'OK'|'ERROR',
            'id': number,
        },
    deleteDownload: {
            'status': 'OK'|'ERROR',
        },
    createKarte: {
            'status': 'OK'|'ERROR',
            'id': number|null,
        },
    getKarte: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzKarteData,
        },
    editKarte: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzKarteData,
        },
    updateKarte: {
            'status': 'OK'|'ERROR',
            'id': number,
        },
    deleteKarte: {
            'status': 'OK'|'ERROR',
        },
    createLink: {
            'status': 'OK'|'ERROR',
            'id': number|null,
        },
    getLink: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzLinkData,
        },
    editLink: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzLinkData,
        },
    updateLink: {
            'status': 'OK'|'ERROR',
            'id': number,
        },
    deleteLink: {
            'status': 'OK'|'ERROR',
        },
    createNews: {
            'status': 'OK'|'DENIED'|'ERROR',
            'id': number|null,
        },
    getNews: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzNewsData,
        },
    editNews: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzNewsData,
        },
    updateNews: {
            'status': 'OK'|'ERROR',
            'id': number,
        },
    deleteNews: {
            'status': 'OK'|'ERROR',
        },
    createRole: {
            'status': 'OK'|'ERROR',
            'id': number|null,
        },
    getRole: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzRoleData,
        },
    editRole: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzRoleData,
        },
    updateRole: {
            'status': 'OK'|'ERROR',
            'id': number,
        },
    deleteRole: {
            'status': 'OK'|'ERROR',
        },
    addUserRoleMembership: {
            'status': 'OK'|'ERROR',
        },
    removeUserRoleMembership: {
            'status': 'OK'|'ERROR',
        },
    getSnippet: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzSnippetData,
        },
    editSnippet: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzSnippetData,
        },
    updateSnippet: {
            'status': 'OK'|'ERROR',
            'id': number,
        },
    createWeeklyPicture: {
            'status': 'OK'|'ERROR',
            'id': number|null,
        },
    getWeeklyPicture: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzWeeklyPictureData,
        },
    editWeeklyPicture: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzWeeklyPictureData,
        },
    updateWeeklyPicture: {
            'status': 'OK'|'ERROR',
            'id': number,
        },
    deleteWeeklyPicture: {
            'status': 'OK'|'ERROR',
        },
    createTermin: {
            'status': 'OK'|'ERROR',
            'id': number|null,
        },
    getTermin: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzTerminData,
        },
    editTermin: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzTerminData,
        },
    updateTermin: {
            'status': 'OK'|'ERROR',
            'id': number,
        },
    deleteTermin: {
            'status': 'OK'|'ERROR',
        },
    createTerminLabel: {
            'status': 'OK'|'ERROR',
            'id': number|null,
        },
    listTerminLabels: {
            'items': Array<{
            'id': number,
            'meta': OlzMetaData,
            'data': OlzTerminLabelData,
        }>,
        },
    getTerminLabel: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzTerminLabelData,
        },
    editTerminLabel: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzTerminLabelData,
        },
    updateTerminLabel: {
            'status': 'OK'|'ERROR',
            'id': number,
        },
    deleteTerminLabel: {
            'status': 'OK'|'ERROR',
        },
    createTerminLocation: {
            'status': 'OK'|'ERROR',
            'id': number|null,
        },
    getTerminLocation: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzTerminLocationData,
        },
    editTerminLocation: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzTerminLocationData,
        },
    updateTerminLocation: {
            'status': 'OK'|'ERROR',
            'id': number,
        },
    deleteTerminLocation: {
            'status': 'OK'|'ERROR',
        },
    createTerminTemplate: {
            'status': 'OK'|'ERROR',
            'id': number|null,
        },
    getTerminTemplate: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzTerminTemplateData,
        },
    editTerminTemplate: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzTerminTemplateData,
        },
    updateTerminTemplate: {
            'status': 'OK'|'ERROR',
            'id': number,
        },
    deleteTerminTemplate: {
            'status': 'OK'|'ERROR',
        },
    createUser: {
            'status': 'OK'|'OK_NO_EMAIL_VERIFICATION'|'DENIED'|'ERROR',
            'id': number|null,
        },
    getUser: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzRoleData,
        },
    editUser: {
            'id': number,
            'meta': OlzMetaData,
            'data': OlzRoleData,
        },
    updateUser: {
            'status': 'OK'|'ERROR',
            'id': number,
        },
    deleteUser: {
            'status': 'OK'|'ERROR',
        },
    createBooking: {
            'status': 'OK'|'ERROR',
            'id': string|null,
        },
    createRegistration: {
            'status': 'OK'|'ERROR',
            'id': string|null,
        },
    getManagedUsers: {
            'status': 'OK'|'ERROR',
            'managedUsers': Array<{
            'id': number,
            'firstName': string,
            'lastName': string,
        }>|null,
        },
    getPrefillValues: {
            'firstName': string,
            'lastName': string,
            'username': string,
            'email': string,
            'phone': string|null,
            'gender': 'M'|'F'|'O'|null,
            'birthdate': string|null,
            'street': string|null,
            'postalCode': string|null,
            'city': string|null,
            'region': string|null,
            'countryCode': string|null,
            'siCardNumber': number|null,
            'solvNumber': string|null,
        },
    getRegistration: {
            'id': string,
            'meta': OlzMetaData,
            'data': OlzRegistrationData,
        },
    executeCommand: {
            'error': boolean,
            'output': string,
        },
    getWebdavAccessToken: {
            'status': 'OK'|'ERROR',
            'token': string|null,
        },
    revokeWebdavAccessToken: {
            'status': 'OK'|'ERROR',
        },
    importTermine: Record<string, never>,
    getLogs: {
            'content': Array<string>,
            'pagination': {
            'previous': string|null,
            'next': string|null,
        },
        },
    getAppMonitoringCredentials: {
            'username': string,
            'password': string,
        },
    updateNotificationSubscriptions: {
            'status': 'OK'|'ERROR',
        },
    searchTransportConnection: {
            'status': 'OK'|'ERROR',
            'suggestions': Array<OlzTransportSuggestion>|null,
        },
    listPanini2024Pictures: Array<{
            'data': OlzPanini2024PictureData,
        }>,
    updateMyPanini2024: {
            'status': 'OK'|'ERROR',
        },
    getMySkillLevels: {[key: string]: {
            'value': number,
        }},
    updateMySkillLevels: {
            'status': 'OK'|'ERROR',
        },
    registerSkillCategories: {
            'idByName': {[key: string]: string},
        },
    registerSkills: {
            'idByName': {[key: string]: string},
        },
    updateResults: {
            'status': 'OK'|'INVALID_FILENAME'|'INVALID_BASE64_DATA'|'ERROR',
        },
    getAppSearchEnginesCredentials: {
            'username': string,
            'password': string,
        },
    getAppStatisticsCredentials: {
            'username': string,
            'password': string,
        },
    getAppYoutubeCredentials: {
            'username': string,
            'password': string,
        },
}

