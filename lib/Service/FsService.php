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

use OCP\Files\IAppData;
use OCP\Files\FileInfo;
use OCP\Files\Node;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use Psr\Log\LoggerInterface;

use OCA\Cloud_Py_API\Proto\FsCreateRequest;
use OCA\Cloud_Py_API\Proto\FsDeleteRequest;
use OCA\Cloud_Py_API\Proto\FsGetInfoReply;
use OCA\Cloud_Py_API\Proto\fsId;
use OCA\Cloud_Py_API\Proto\FsListReply;
use OCA\Cloud_Py_API\Proto\FsListRequest;
use OCA\Cloud_Py_API\Proto\FsMoveRequest;
use OCA\Cloud_Py_API\Proto\FsReadReply;
use OCA\Cloud_Py_API\Proto\FsReadRequest;
use OCA\Cloud_Py_API\Proto\FsReply;
use OCA\Cloud_Py_API\Proto\FsWriteRequest;


class FsService {

	/** @var IRootFolder */
	private $rootFolder;

	/** @var IAppData */
	private $appData;

	public function __construct(IAppData $appData, IRootFolder $rootFolder,
								LoggerInterface $logger)
	{
		$this->rootFolder = $rootFolder;
		$this->appData = $appData;
		$this->logger = $logger;
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
		$dirId = $fsId->getFileId();
		$userId = $fsId->getUserId();
		$userFolder = $this->rootFolder->getUserFolder($userId);
		$nodes = $userFolder->getById($dirId);
		$response = new FsListReply();
		$responseNodes = array();
		$response->setNodes($responseNodes);
		if (count($nodes) === 1) {
			/** @var Folder */
			$folder = $nodes[0];
			$dirNodes = $folder->getDirectoryListing();
			/** @var Node */
			foreach ($dirNodes as $node) {
				$fsGetInfoReply = new FsGetInfoReply();
				$nodeFsId = new fsId();
				$nodeFsId->setFileId($node->getId());
				$nodeFsId->setUserId($node->getOwner()->getUID());
				$fsGetInfoReply->setFileId($nodeFsId);
				$fsGetInfoReply->setIsDir($node->getType() === FileInfo::TYPE_FOLDER);
				$fsGetInfoReply->setIsLocal(true);
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
				$fsGetInfoReply->setMountId($node->getMountPoint()->getMountId() !== null ? $node->getMountPoint()->getMountId() : -1);
				array_push($responseNodes, $fsGetInfoReply);
			}
			$response->setNodes($responseNodes);
		}
		return $response;
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