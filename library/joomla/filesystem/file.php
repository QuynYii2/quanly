<?php
/**
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once "path.php";

/**
 * A File handling class
 *
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 * @since       11.1
 */
class JFile
{
	/**
	 * Gets the extension of a file name
	 *
	 * @param   string  $file  The file name
	 *
	 * @return  string  The file extension
	 *
	 * @since   11.1
	 */
	public static function getExt($file)
	{
		$dot = strrpos($file, '.') + 1;

		return substr($file, $dot);
	}

	/**
	 * Strips the last extension off of a file name
	 *
	 * @param   string  $file  The file name
	 *
	 * @return  string  The file name without the extension
	 *
	 * @since   11.1
	 */
	public static function stripExt($file)
	{
		return preg_replace('#\.[^.]*$#', '', $file);
	}

	/**
	 * Makes file name safe to use
	 *
	 * @param   string  $file  The name of the file [not full path]
	 *
	 * @return  string  The sanitised string
	 *
	 * @since   11.1
	 */
	public static function makeSafe($file)
	{
		$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');

		return preg_replace($regex, '', $file);
	}

	/**
	 * Copies a file
	 *
	 * @param   string   $src          The path to the source file
	 * @param   string   $dest         The path to the destination file
	 * @param   string   $path         An optional base path to prefix to the file names
	 * @param   boolean  $use_streams  True to use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function copy($src, $dest, $path = null, $use_streams = false)
	{
		// Prepend a base path if it exists
		if ($path)
		{
			$src = JPath::clean($path . '/' . $src);
			$dest = JPath::clean($path . '/' . $dest);
		}

		// Check src path
		if (!is_readable($src))
		{
			#JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_JFILE_FIND_COPY', $src), #JLog::WARNING, 'jerror');
			return false;
		}

		if (!@ copy($src, $dest))
		{
			#JLog::add(JText::_('JLIB_FILESYSTEM_ERROR_COPY_FAILED'), #JLog::WARNING, 'jerror');
			return false;
		}
		$ret = true;

		return $ret;
	}

	/**
	 * Delete a file or array of files
	 *
	 * @param   mixed  $file  The file name or an array of file names
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function delete($file)
	{
		if (is_array($file))
		{
			$files = $file;
		}
		else
		{
			$files[] = $file;
		}

		foreach ($files as $file)
		{
			$file = JPath::clean($file);

			// Try making the file writable first. If it's read-only, it can't be deleted
			// on Windows, even if the parent folder is writable
			@chmod($file, 0777);

			// In case of restricted permissions we zap it one way or the other
			// as long as the owner is either the webserver or the ftp
			if (@unlink($file))
			{
				// Do nothing
			}
			else
			{
				$filename = basename($file);
				#JLog::add(JText::sprintf('JLIB_FILESYSTEM_DELETE_FAILED', $filename), #JLog::WARNING, 'jerror');
				return false;
			}
		}

		return true;
	}

	/**
	 * Moves a file
	 *
	 * @param   string   $src          The path to the source file
	 * @param   string   $dest         The path to the destination file
	 * @param   string   $path         An optional base path to prefix to the file names
	 * @param   boolean  $use_streams  True to use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function move($src, $dest, $path = '', $use_streams = false)
	{
		if ($path)
		{
			$src = JPath::clean($path . '/' . $src);
			$dest = JPath::clean($path . '/' . $dest);
		}

		// Check src path
		if (!is_readable($src))
		{

			#return JText::_('JLIB_FILESYSTEM_CANNOT_FIND_SOURCE_FILE');
		}

				if (!@ rename($src, $dest))
				{
					#JLog::add(JText::_('JLIB_FILESYSTEM_ERROR_RENAME_FILE'), #JLog::WARNING, 'jerror');
					return false;
				}

			return true;
	}

	/**
	 * Read the contents of a file
	 *
	 * @param   string   $filename   The full file path
	 * @param   boolean  $incpath    Use include path
	 * @param   integer  $amount     Amount of file to read
	 * @param   integer  $chunksize  Size of chunks to read
	 * @param   integer  $offset     Offset of the file
	 *
	 * @return  mixed  Returns file contents or boolean False if failed
	 *
	 * @since   11.1
	 * @deprecated  13.3  Use the native file_get_contents() instead.
	 */
	public static function read($filename, $incpath = false, $amount = 0, $chunksize = 8192, $offset = 0)
	{
		#JLog::add(__METHOD__ . ' is deprecated. Use native file_get_contents() syntax.', #JLog::WARNING, 'deprecated');

		$data = null;
		if ($amount && $chunksize > $amount)
		{
			$chunksize = $amount;
		}

		if (false === $fh = fopen($filename, 'rb', $incpath))
		{
			#JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_READ_UNABLE_TO_OPEN_FILE', $filename), #JLog::WARNING, 'jerror');
			return false;
		}

		clearstatcache();

		if ($offset)
		{
			fseek($fh, $offset);
		}

		if ($fsize = @ filesize($filename))
		{
			if ($amount && $fsize > $amount)
			{
				$data = fread($fh, $amount);
			}
			else
			{
				$data = fread($fh, $fsize);
			}
		}
		else
		{
			$data = '';

			/*
			 * While it's:
			 * 1: Not the end of the file AND
			 * 2a: No Max Amount set OR
			 * 2b: The length of the data is less than the max amount we want
			 */
			while (!feof($fh) && (!$amount || strlen($data) < $amount))
			{
				$data .= fread($fh, $chunksize);
			}
		}
		fclose($fh);

		return $data;
	}

	/**
	 * Write contents to a file
	 *
	 * @param   string   $file         The full file path
	 * @param   string   &$buffer      The buffer to write
	 * @param   boolean  $use_streams  Use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function write($file, &$buffer, $use_streams = false)
	{
		@set_time_limit(ini_get('max_execution_time'));

		// If the destination directory doesn't exist we need to create it
		if (!file_exists(dirname($file)))
		{
			require_once 'folder.php';
			JFolder::create(dirname($file));
		}

		$file = JPath::clean($file);
		$ret = is_int(file_put_contents($file, $buffer)) ? true : false;

		return $ret;
	}

	/**
	 * Moves an uploaded file to a destination folder
	 *
	 * @param   string   $src          The name of the php (temporary) uploaded file
	 * @param   string   $dest         The path (including filename) to move the uploaded file to
	 * @param   boolean  $use_streams  True to use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function upload($src, $dest, $use_streams = false)
	{
		// Ensure that the path is valid and clean
		$dest = JPath::clean($dest);

		// Create the destination directory if it does not exist
		$baseDir = dirname($dest);

		if (!file_exists($baseDir))
		{
			require_once 'folder.php';
			JFolder::create($baseDir);
		}

		if (is_writeable($baseDir) && move_uploaded_file($src, $dest))
		{
			// Short circuit to prevent file permission errors
			if (JPath::setPermissions($dest))
			{
				$ret = true;
			}
			else
			{
				#JLog::add(JText::_('JLIB_FILESYSTEM_ERROR_WARNFS_ERR01'), #JLog::WARNING, 'jerror');
			}
		}
		else
		{
			#JLog::add(JText::_('JLIB_FILESYSTEM_ERROR_WARNFS_ERR02'), #JLog::WARNING, 'jerror');
		}

		return $ret;
	}

	/**
	 * Wrapper for the standard file_exists function
	 *
	 * @param   string  $file  File path
	 *
	 * @return  boolean  True if path is a file
	 *
	 * @since   11.1
	 */
	public static function exists($file)
	{
		return is_file(JPath::clean($file));
	}

	/**
	 * Returns the name, without any path.
	 *
	 * @param   string  $file  File path
	 *
	 * @return  string  filename
	 *
	 * @since   11.1
	 * @deprecated  13.3 Use basename() instead.
	 */
	public static function getName($file)
	{
		#JLog::add(__METHOD__ . ' is deprecated. Use native basename() syntax.', #JLog::WARNING, 'deprecated');

		// Convert back slashes to forward slashes
		$file = str_replace('\\', '/', $file);
		$slash = strrpos($file, '/');
		if ($slash !== false)
		{

			return substr($file, $slash + 1);
		}
		else
		{

			return $file;
		}
	}
}
