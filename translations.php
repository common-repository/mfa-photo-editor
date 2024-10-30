<?php
/**
 *
 * THIS FILE MUST BE LOCATED IN THE PLUGIN FOLDER
 *
 *
 * **************************************************************** *
 *                                                                  *
 *  ALL THE STRINGS 'PLUGIN' MUST BE REPLACED WITH THE TEXT DOMAIN  *
 *                                                                  *
 * ******************************************************************
 *
 */
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

	/** @var string $key is defined in the function SOS\WP\Translation::t_() that includes this file */
	switch( $key )
	{
		/*
		case 'example':
			return _x( 'key', 'context', 'PLUGIN' );
		*/

        //BACKEND

        case 'page.gallery.title':
            /* translators: [text] title of the gallery administration page */
            return _x( 'Photo Editor for My FastAPP', 'Backend', 'mfa-photo-editor' );

        case 'info.add.images':
            /* translators: [text] info about how to select the images to be added to the gallery */
            return _x( 'Click the &nbsp;{icon}&nbsp; icons to select the images from the Wordpress Media Library.', 'Backend', 'mfa-photo-editor' );

        case 'info.save.gallery':
            /* translators: [text] info about how to save the gallery configuration */
            return _x( 'Click the &nbsp;{button}&nbsp; button to save the Overlay and Scenario configurations.', 'Backend', 'mfa-photo-editor' );

        case 'info.copy.url':
            /* translators: [text] info about how to copy the configuration file into the clipboard */
            return _x( 'Click the &nbsp;{icon}&nbsp; icon to copy the URL of the gallery configuration file into the clipboard.', 'Backend', 'mfa-photo-editor' );

        case 'info.remove.image':
            /* translators: [text] info about how to remove an image from the gallery */
            return _x( 'To remove an image, click the &nbsp;{icon}&nbsp; icon and then save changes.', 'Backend', 'mfa-photo-editor' );

        case 'info.sort.images':
            /* translators: [text] info about how to sort the gallery images */
            return _x( 'Drag and drop the images to sort their order.', 'Backend', 'mfa-photo-editor' );

        case 'info.show.hide':
            /* translators: [text] tooltip for the help icon button */
            return _x( 'Show/Hide the instructions.', 'Backend', 'mfa-photo-editor' );

        case 'label.gallery.url':
            /* translators: [text] label of the field Gallery URL (url of the json file) */
            return _x( 'Gallery URL', 'Backend', 'mfa-photo-editor' );

        case 'text.copy.url':
            /* translators: [text] tooltip of the 'copy into clipboard' icon */
            return _x( 'copy into clipboard', 'Backend', 'mfa-photo-editor' );

        case 'button.text.save.gallery':
            /* translators: [text] text of the save gallery button */
            return _x( 'save changes', 'Backend', 'mfa-photo-editor' );


        case 'js.msg.problem.generic':
            /* translators: [message] generic message for javascript problem  */
            return _x( 'A javascript problem occurred.', 'Backend', 'mfa-photo-editor' );

        case 'js.msg.copy.to.clipboard':
            /* translators: [message] appears after clicking the button  */
            return _x( 'Text copied into clipboard:', 'Backend', 'mfa-photo-editor' );

        case 'js.media-lib.button':
            /* translators: [text] button of WP media library  */
            return _x( 'OK', 'Backend', 'mfa-photo-editor' );

        case 'js.media-lib.overlay.title':
            /* translators: [text] title of WP media library  */
            return _x( 'Select the image(s) for the OVERLAY', 'Backend', 'mfa-photo-editor' );

        case 'js.media-lib.scenario.title':
            /* translators: [text] title of WP media library  */
            return _x( 'Select the image(s) for the SCENARIO', 'Backend', 'mfa-photo-editor' );

        case 'js.image.title':
            /* translators: [text] title of the gallery images  */
            return _x( 'drag&drop to sort', 'Backend', 'mfa-photo-editor' );

        case 'js.ico-trash.delete':
            /* translators: [text] title of the trash icon on each gallery image  */
            return _x( 'delete image', 'Backend', 'mfa-photo-editor' );

        case 'js.ico-trash.undo':
            /* translators: [text] title of the trash icon on each deleting gallery image  */
            return _x( 'undo deleting', 'Backend', 'mfa-photo-editor' );


        //REST API

        case 'api.request.body.empty':
            /* translators: [message] error: http request with no body  */
            return _x( 'The request body is empty.', 'Rest API', 'mfa-photo-editor' );

        case 'api.request.data.invalid':
            /* translators: [message] error: http request body data are invalid  */
            return _x( 'Invalid data in the request body.', 'Rest API', 'mfa-photo-editor' );

        case 'api.gallery.save.ok':
            /* translators: [message] info: gallery has been saved successfully  */
            return _x( 'The gallery configuration has been saved.', 'Rest API', 'mfa-photo-editor' );

        case 'api.data.save.problem':
            /* translators: [message] error: problem saving configuration in the database  */
            return _x( 'A problem occurred while saving data in the WP database.', 'Rest API', 'mfa-photo-editor' );

        case 'api.file.save.problem':
            /* translators: [message] error: problem saving configuration in the json file  */
            return _x( 'A problem occurred while saving data in the configuration json file.', 'Rest API', 'mfa-photo-editor' );


        /* DO NOT ELIMINATE THE FOLLOWING CASES */

		case 'SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\MetaBox::checkSave::nonce-invalid':
			/* translators: [message] problem (invalid nonce) while checking the metabox data */
			return _x('A security problem occurred while checking a metabox data', 'Backend', 'mfa-photo-editor'); //Si è vericato un problema di sicurezza durante la verifica dei dati di una metabox
		case 'SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\MetaBox::checkSave::user-unauthorized':
			/* translators: [message] the user has insufficient rights to save the metabox data */
			return _x("You're not authorized to modify metabox content", 'Backend', 'mfa-photo-editor'); //Non sei autorizzato a modificare il contenuto di una metabox

		case 'Translator':
			/* translators: User role for subscribers. */
			return _x( 'Translator', 'User role', 'mfa-photo-editor' );


		//DEFAULT DOMAIN

		case 'Administrator':
			/* translators: User role for administrators. */
			return _x( 'Administrator', 'User role' );
		case 'Editor':
			/* translators: User role for editors. */
			return _x( 'Editor', 'User role' );
		case 'Author':
			/* translators: User role for authors. */
			return _x( 'Author', 'User role' );
		case 'Contributor':
			/* translators: User role for contributors. */
			return _x( 'Contributor', 'User role' );
		case 'Subscriber':
			/* translators: User role for subscribers. */
			return _x( 'Subscriber', 'User role' );

		default:
			return $key; //the key has not be handled
	}

?>