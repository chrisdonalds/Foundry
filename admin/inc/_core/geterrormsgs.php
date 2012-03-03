<?php
// ---------------------------
//
// ADMIN ERROR MESSAGES LIST
//
// ---------------------------

// success messages
define('SUCCESS_CREATE', 'The record created successfully. ');
define('SUCCESS_EDIT', 'The record updated successfully. ');
define('SUCCESS_EDITGROUP', 'All entries updated successfully. ');
define('SUCCESS_DELETE', 'The record deleted successfully. ');
define('SUCCESS_ACTIVATE', 'The record activated successfully. ');
define('SUCCESS_DEACTIVATE', 'The record deactivated successfully. ');

// error messages
define('LOGIN_ERROR', 'Invalid username or password, or your account was not found. ');
define('DUPLICATE_USERNAME', 'The username entered already exists. ');
define('DUPLICATE_RECORD', 'Another %s already exists%s.  Please choose a different %s. ');
define('FAILURE_CREATE', 'The record was not created successfully. ');
define('FAILURE_EDIT', 'The record was not updated successfully. ');
define('FAILURE_EDITGROUP', 'One or more entries were not updated successfully. ');
define('FAILURE_DELETE', 'The record was not deleted successfully. ');
define('FAILURE_ACTIVATE', 'The record was not activated successfully. ');
define('FAILURE_DEACTIVATE', 'The record was not deactivated successfully. ');
define('MISSING_ARG', 'CORE: Missing argument for function: ');
define('ACCESS_PAGE_FAIL', 'You do not have the appropriate authorization to access this page.');
define('ACCESS_FUNC_FAIL', 'You do not have the appropriate authorization to access the requested operation.');

// DB errors
define('DB_DELETE_ERROR', 'DB: Deletion error. ');
define('DB_UPDATE_ERROR', 'DB: Updating error. ');
define('DB_SELECT_ERROR', 'DB: Selecting error. ');

// media errors
define('IMG_BAD_FORMAT', 'FILE: Invalid file format. ');
define('IMG_TOO_BIG', 'The uploaded file size exceeds limit');
define('IMG_DIM_TOO_BIG', 'The image [image] exceeds the maximum allowed dimensions');
define('IMG_DIM_WRONG_SIZE', 'The image [image] must be exactly the required dimensions');
define('IMG_FAILURE_COPY', 'File upload failed copying file to ');
define('IMG_PATH_UNWRITABLE', 'The upload path is not writable, please check permissions. ');
define('IMG_NOT_FOUND', 'Cannot find ');
define('IMG_NO_FILENAME', 'No filename provided. ');
define('IMG_CREATE_ERROR', 'Cannot create image GD. ');
define('IMG_RESIZE_ERROR', 'Cannot resize image GD. ');
?>