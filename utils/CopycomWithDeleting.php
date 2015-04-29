<?php
namespace sk;

use phpbu\App\Backup\Sync\Copycom;
use phpbu\App\Result;
use phpbu\App\Backup;
use phpbu\App\Backup\Target;
use phpbu\App\Exception;
use Barracuda\Copy\API as CopycomApi;

/**
 * Copycom with remote files deleting
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Sergej Karavajnij <basyanya@gmail.com>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.1.2
 */
class CopycomWithDeleting extends Copycom
{

	/** Sync and delete
	 * 
	 * @param Target $target
	 * @param Result $result
	 *
	 * @throws Backup\Sync\Exception
	 * @throws Exception
	 */

    public function sync(Target $target, Result $result)
    {
	    parent::sync($target, $result);

	    $copy = new CopycomApi($this->appKey, $this->appSecret, $this->userKey, $this->userSecret);
	    $remoteFiles = $copy->listPath($this->path);
	    
	    $collector = new Backup\Collector($target);
	    $currentFiles = $collector->getBackupFiles();

	    $filesToDelete = $this->createFilesListToDelete($remoteFiles, $currentFiles, $target);
	    
	    foreach ($filesToDelete as $fileName) {
		    try {
			    $copy->removeFile($this->path . $fileName);
		    } catch (\Exception $e) {
			    throw new Exception($e->getMessage(), null, $e);
		    }
	    }
	    
    }

	/** Creating files list to delete from Copy.com
	 * 
	 * @param array  $remoteFiles
	 * @param array  $currentFiles
	 * @param Target $target
	 *
	 * @return array
	 */
	
	private function createFilesListToDelete(array $remoteFiles, array $currentFiles, Target $target) {
		$currentFilesList = $remoteFilesList = array();
		
		foreach ($remoteFiles as $obj) { $remoteFilesList[] = basename($obj->path); }
		foreach ($currentFiles as $obj) { $currentFilesList[] = $obj->getFilename(); }
		$currentFilesList[] = $target->getFilename();
		
		return array_diff($remoteFilesList, $currentFilesList);
	}
	
}
