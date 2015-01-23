<?php

// Prevent direct file access
if( ! defined("MCE4M_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
* Handles the uploading of the media file to be exchanged for email
*/
class MCE4M_Media_Uploader {

	/**
	* Holds information about the media file being uploaded
	* @var array
	*/
	private $file_data;

	/**
	* Holds the raw file data
	* @var string
	*/
	private $file_raw_data;

	/**
	* Holds the AWS bucket name
	* @var string
	*/
	private $bucket;

	/**
	* Holds the S3 PHP API
	* @var S3
	*/
	private $s3;

	/**
	* Constructor
	*/
	public function __construct() {

		// initialize s3 api
		$this->s3 = new S3( mce4m_get_aws_key_id(), mce4m_get_aws_private_key() );

		// save file info
		$this->file_data = array(
			'name' => $_FILES['files']['name'][0],
			'tmp_name' => $_FILES['files']['tmp_name'][0],
		);

		// get raw file data
		$this->file_raw_data = file_get_contents( $this->file_data['tmp_name'] );

		// the aws bucket name
		$this->bucket = mce4m_get_options()['aws_bucket_name'];

		// get file MIME type using finfo (more secure than type reported in $_FILES)
		$finfo = finfo_open( FILEINFO_MIME );
		if ( !$finfo ) {
			error_log('MCE4M_Media_Uploader::construct():  Error opening file info database.');
		}
		$this->file_data['mime_type'] = finfo_file( $finfo, $this->file_data['tmp_name'] );
		finfo_close($finfo);

		// calculate size
		$s = round( ( ($_FILES['files']['size'][0] / 1024 ) / 1024 ), 1);
		$s = strval( $s ) . 'M';
		$this->file_data['size'] = $s;

		// now link upload function to WP 'init' action for processing later
		add_action( 'init', array( $this, 'handle_media_upload' ) );

	}

	/**
	* Saves the file data to the server
	*/
	public function handle_media_upload() {

		if ( !isset( $this->file_data ) || !isset( $this->file_raw_data ) )
		{
			error_log('MCE4M_Media_Uploader::handle_media_upload():  [Media Upload Error] Some of the file data was not set.');
			return false;
		}

		// send file to S3 bucket
		$result = $this->s3->putObject(
			$this->file_raw_data,
			$this->bucket,
			'mce4m/' . $this->file_data['name'],
			S3::ACL_PUBLIC_READ,
			array(),
			array( 'Content-Type' => $this->file_data['mime_type'] )
		);

		// prepare data to return to client
		if ( $result ) {
			$result = $this->file_data;
			$result['url'] = $this->s3->getAuthenticatedURL( 
				$this->bucket,
				'mce4m/' . $this->file_data['name'],
				(60 * 60) * 60
			);
			error_log(print_r($result,true));
		}

		exit( json_encode( $result ) );

	}

}