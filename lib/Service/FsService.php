<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2021 Andrey Borysenko <andrey18106x@gmail.com>
 * 
 * @copyright Copyright (c) 2021 Alexander Piskun <bigcat88@icloud.com>
 * 
 * @author 2021 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Cloud_Py_API\Service;

use OCP\Files\FileInfo;
use OCP\Files\Node;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;

use OCA\Cloud_Py_API\Proto\FsCreateRequest;
use OCA\Cloud_Py_API\Proto\FsDeleteRequest;
use OCA\Cloud_Py_API\Proto\FsGetInfoRequest;
use OCA\Cloud_Py_API\Proto\fsId;
use OCA\Cloud_Py_API\Proto\FsListReply;
use OCA\Cloud_Py_API\Proto\FsListRequest;
use OCA\Cloud_Py_API\Proto\FsMoveRequest;
use OCA\Cloud_Py_API\Proto\FsNodeInfo;
use OCA\Cloud_Py_API\Proto\FsReadReply;
use OCA\Cloud_Py_API\Proto\FsReadRequest;
use OCA\Cloud_Py_API\Proto\FsReply;
use OCA\Cloud_Py_API\Proto\FsWriteRequest;


class FsService {

	/** @var IRootFolder */
	private $rootFolder;

	public function __construct(IRootFolder $rootFolder)
	{
		$this->rootFolder = $rootFolder;
	}

	/**
	 * FS Get File info
	 * 
	 * @param FsGetInfoRequest $request
	 * 
	 * @return FsListReply|null FS FileInfo result
	 */
	public function info(FsGetInfoRequest $request): ?FsListReply {
		$fsId = $request->getFileId();
		$fileId = $fsId->getFileId();
		$userId = $fsId->getUserId();
		$userFolder = $this->rootFolder->getUserFolder($userId);
		$nodes = $userFolder->getById($fileId);
		$response = new FsListReply();
		$responseNodes = array();
		if (count($nodes) === 1 && isset($nodes[0]) && $nodes[0] instanceof File) {
			/** @var File $file */
			$file = $nodes[0];
			$fsNodeInfo = $this->getFsNodeInfo($file);
			array_push($responseNodes, $fsNodeInfo);
		}
		$response->setNodes($responseNodes);
		return $response;
	}

	/**
	 * FS List directory
	 * 
	 * @param FsListRequest $request
	 * 
	 * @return FsListReply|null FS List directory result
	 */
	public function list(FsListRequest $request): ?FsListReply {
		$fsId = $request->getDirId();
		$userId = $fsId->getUserId();
		$dirId = $fsId->getFileId();
		/** @var Folder */
		$userFolder = $this->rootFolder->getUserFolder($userId);
		$response = new FsListReply();
		$responseNodes = array();
		if (isset($dirId) && $dirId !== 0) {
			$nodes = $userFolder->getById($dirId);
			if (count($nodes) === 1 && isset($nodes[0]) && $nodes[0] instanceof Folder) {
				/** @var Folder $folder */
				$folder = $nodes[0];
				$dirNodes = $folder->getDirectoryListing();
				/** @var Node */
				foreach ($dirNodes as $node) {
					$fsNodeInfo = $this->getFsNodeInfo($node);
					array_push($responseNodes, $fsNodeInfo);
				}
			}
		} else {
			$nodes = $userFolder->getDirectoryListing();
			foreach ($nodes as $node) {
				$fsNodeInfo = $this->getFsNodeInfo($node);
				array_push($responseNodes, $fsNodeInfo);
			}
		}
		$response->setNodes($responseNodes);
		return $response;
	}

	private function getFsNodeInfo(Node $node): FsNodeInfo {
		$fsGetInfoReply = new FsNodeInfo();
		$nodeFsId = new fsId();
		$nodeFsId->setFileId($node->getId());
		$nodeFsId->setUserId($node->getOwner()->getUID());
		$fsGetInfoReply->setFileId($nodeFsId);
		$fsGetInfoReply->setIsDir($node->getType() === FileInfo::TYPE_FOLDER);
		$fsGetInfoReply->setIsLocal($node->getStorage()->isLocal());
		$fsGetInfoReply->setMimetype($node->getMimetype());
		$fsGetInfoReply->setName($node->getName());
		$fsGetInfoReply->setInternalPath($node->getInternalPath());
		$fsGetInfoReply->setAbsPath($node->getPath());
		$fsGetInfoReply->setSize($node->getSize());
		$fsGetInfoReply->setPermissions($node->getPermissions());
		$fsGetInfoReply->setMtime($node->getMTime());
		$fsGetInfoReply->setChecksum($node->getChecksum());
		$fsGetInfoReply->setEncrypted($node->isEncrypted());
		$fsGetInfoReply->setEtag($node->getEtag());
		$fsGetInfoReply->setOwnerName($node->getOwner()->getUID());
		$fsGetInfoReply->setStorageId($node->getStorage()->getId());
		if ($node->getMountPoint()->getMountId() !== null) {
			$fsGetInfoReply->setMountId($node->getMountPoint()->getMountId());
		}
		return $fsGetInfoReply;
	}

	/**
	 * FS Read file
	 * 
	 * @param FsReadRequest $request
	 * 
	 * @return FsReadReply|null FS Read file contents
	 */
	public function read(FsReadRequest $request): ?FsReadReply {
		return new FsReadReply(null);
	}

	/**
	 * FS Write file
	 * 
	 * @param FsWriteRequest $request
	 * 
	 * @return FsReply|null FS Write file results
	 */
	public function write(FsWriteRequest $request): ?FsReply {
		return new FsReply(null);
	}

	/**
	 * FS Create method
	 * 
	 * @param FsCreateRequest $params
	 * 
	 * @return FsReply|null FS Create results
	 */
	public function create(FsCreateRequest $request): ?FsReply {
		return new FsReply(null);
	}

	/**
	 * FS Delete method
	 * 
	 * @param FsDeleteRequest $request
	 * 
	 * @return FsReply|null FS Delete results
	 */
	public function delete(FsDeleteRequest $request): ?FsReply {
		return new FsReply(null);
	}

	/**
	 * FS Move method
	 * 
	 * @param FsMoveRequest $request
	 * 
	 * @return FsReply FS Move results
	 */
	public function move(FsMoveRequest $request): ?FsReply {
		return new FsReply(null);
	}

}