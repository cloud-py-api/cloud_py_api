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

use Grpc\ServerCallReader;
use Grpc\ServerCallWriter;
use OCP\Files\FileInfo;
use OCP\Files\Node;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Lock\LockedException;
use OCP\Files\GenericFileException;

use OCA\Cloud_Py_API\Proto\FsCreateRequest;
use OCA\Cloud_Py_API\Proto\FsCreateReply;
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
use OCA\Cloud_Py_API\Proto\fsResultCode;
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
	 * @param ServerCallWriter $writer
	 */
	public function read(FsReadRequest $request, ServerCallWriter $writer): void {
		$fsId = $request->getFileId();
		$fileId = $fsId->getFileId();
		$userId = $fsId->getUserId();
		$offset = $request->getOffset();
		$length = $request->getBytesToRead();
		$userFolder = $this->rootFolder->getUserFolder($userId);
		$response = new FsReadReply();
		$nodes = $userFolder->getById($fileId);
		if (count($nodes) === 1 && isset($nodes[0]) && $nodes[0] instanceof File) {
			/** @var File */
			$file = $nodes[0];
			if ($offset === 0 && $length === 0) {
				try {
					if ($file->getSize() / pow(1024, 3) <= 2) {
						/** @var FsReadReply */
						$response->setLast(true);
						$response->setContent($file->getContent());
						$response->setResCode(fsResultCode::NO_ERROR);
					}
					// TODO Add batch reading if 2GB GRPC packet size limit exceed
				} catch (NotPermittedException | LockedException $e) {
					if ($e instanceof NotPermittedException) {
						$response->setResCode(fsResultCode::NOT_PERMITTED);
					} else if ($e instanceof NotPermittedException) {
						$response->setResCode(fsResultCode::LOCKED);
					}
				}
			} else {
				try {
					$handle = $file->fopen('r');
					if ($handle) {
						if (fseek($handle, $offset) === 0) {
							$response->setLast(true);
							$data = fread($handle, $length);
							if ($data !== false) {
								$response->setContent($data);
								$response->setResCode(fsResultCode::NO_ERROR);
							}
							$writer->write($response);
							fclose($handle);
						}
					}
				} catch (NotPermittedException | LockedException $e) {
					if ($e instanceof NotPermittedException) {
						$response->setResCode(fsResultCode::NOT_PERMITTED);
					} else if ($e instanceof NotPermittedException) {
						$response->setResCode(fsResultCode::LOCKED);
					}
				}
			}
		}
		$writer->write($response);
		$writer->finish();
	}

	/**
	 * FS Write file
	 * 
	 * @param ServerCallReader $reader
	 * 
	 * @return FsReply|null FS Write file results
	 */
	public function write(ServerCallReader $reader): ?FsReply {
		$handle = null;
		$response = new FsReply();
		$response->setResCode(fsResultCode::NO_ERROR);
		/** @var FsWriteRequest $request */
		while($request = $reader->read()) {
			if ($handle === null) {
				$fsId = $request->getFileId();
				$fileId = $fsId->getFileId();
				$userId = $fsId->getUserId();
				$userFolder = $this->rootFolder->getUserFolder($userId);
				$nodes = $userFolder->getById($fileId);
				if (count($nodes) === 1 && isset($nodes[0]) && $nodes[0] instanceof File) {
					/** @var File */
					$file = $nodes[0];
					try {
						$handle = $file->fopen('w');
					} catch (NotPermittedException | GenericFileException | LockedException $e) {
						if ($e instanceof NotPermittedException) {
							$response->setResCode(fsResultCode::NOT_PERMITTED);
						} else if ($e instanceof LockedException) {
							$response->setResCode(fsResultCode::LOCKED);
						} else if ($e instanceof GenericFileException) {
							$response->setResCode(fsResultCode::IO_ERROR);
						}
					}
				}
			}
			if ($handle) {
				$data = $request->getContent();
				if (fwrite($handle, $data) === false) {
					$response->setResCode(fsResultCode::IO_ERROR);
				} else {
					$response->setResCode(fsResultCode::NO_ERROR);
				}
				if ($request->getLast()) {
					break;
				}
			}
		}
		fclose($handle);
		return $response;
	}

	/**
	 * FS Create method
	 * 
	 * @param FsCreateRequest $params
	 * 
	 * @return FsCreateReply|null FS Create results
	 */
	public function create(FsCreateRequest $request): ?FsCreateReply {
		/** @var fsId */
		$parentDirFsId = $request->getParentDirId();
		$userId = $parentDirFsId->getUserId();
		$parentDirId = $parentDirFsId->getFileId();
		/** @var Folder */
		$userFolder = $this->rootFolder->getUserFolder($userId);
		$response = new FsCreateReply();
		$fsId = new fsId();
		$fsId->setUserId($userId);
		if ($parentDirId !== null) {
			$nodes = $userFolder->getById($parentDirId);
			if (count($nodes) === 1 && isset($nodes[0]) && $nodes[0] instanceof Folder) {
				/** @var Folder */
				$folder = $nodes[0];
				try {
					if ($request->getIsFile()) {
						$newFile = $folder->newFile($request->getName(), $request->getContent());
						$fsId->setFileId($newFile->getId());
						$response->setFileId($fsId);
					} else {
						$newFolder = $folder->newFolder($request->getName());
						$fsId->setFileId($newFolder->getId());
						$response->setFileId($fsId);
					}
					$response->setResCode(fsResultCode::NO_ERROR);
				} catch (NotPermittedException $e) {
					$response->setResCode(fsResultCode::NOT_FOUND);
				}
			}
		} else {
			try {
				if ($request->getIsFile()) {
					$newFile = $userFolder->newFile($request->getName(), $request->getContent());
					$fsId->setFileId($newFile->getId());
					$response->setFileId($fsId);
				} else {
					$newFolder = $userFolder->newFolder($request->getName());
					$fsId->setFileId($newFolder->getId());
					$response->setFileId($fsId);
				}
				$response->setResCode(fsResultCode::NO_ERROR);
			} catch (NotPermittedException $e) {
				$response->setResCode(fsResultCode::NOT_PERMITTED);
			}
		}
		return $response;
	}

	/**
	 * FS Delete method
	 * 
	 * @param FsDeleteRequest $request
	 * 
	 * @return FsReply|null FS Delete results
	 */
	public function delete(FsDeleteRequest $request): ?FsReply {
		$fsId = $request->getFileId();
		$nodeId = $fsId->getFileId();
		$userId = $fsId->getUserId();
		$userFolder = $this->rootFolder->getUserFolder($userId);
		$response = new FsReply();
		$nodes = $userFolder->getById($nodeId);
		if (count($nodes) === 1 && isset($nodes[0])) {
			/** @var Node */
			$node = $nodes[0];
			try {
				$node->delete();
				$response->setResCode(fsResultCode::NO_ERROR);
			} catch (NotPermittedException | InvalidPathException | NotFoundException $e) {
				if ($e instanceof NotPermittedException) {
					$response->setResCode(fsResultCode::NOT_PERMITTED);
				} else if ($e instanceof InvalidPathException || $e instanceof NotFoundException) {
					$response->setResCode(fsResultCode::NOT_FOUND);
				}
			}
		} else {
			$response->setResCode(fsResultCode::NOT_FOUND);
		}
		return $response;
	}

	/**
	 * FS Move method
	 * 
	 * @param FsMoveRequest $request
	 * 
	 * @return FsReply FS Move results
	 */
	public function move(FsMoveRequest $request): ?FsReply {
		$fsId = $request->getFileId();
		$userId = $fsId->getUserId();
		$nodeId = $fsId->getFileId();
		$userFolder = $this->rootFolder->getUserFolder($userId);
		$response = new FsReply();
		$nodes = $userFolder->getById($nodeId);
		if (count($nodes) === 1 && isset($nodes[0])) {
			/** @var Node */
			$node = $nodes[0];
			if ($request->getCopy()) {
				$node->copy($request->getTargetPath());
				$response->setResCode(fsResultCode::NO_ERROR);
			} else {
				try {
					$node->move($request->getTargetPath());
					$response->setResCode(fsResultCode::NO_ERROR);
				} catch (NotPermittedException | NotFoundException | LockedException $e) {
					if ($e instanceof NotPermittedException) {
						$response->setResCode(fsResultCode::NOT_PERMITTED);
					} else if ($e instanceof NotFoundException) {
						$response->setResCode(fsResultCode::NOT_FOUND);
					} else if ($e instanceof LockedException) {
						$response->setResCode(fsResultCode::LOCKED);
					}
				}
			}
		} else {
			$response->setResCode(fsResultCode::NOT_FOUND);
		}
		return $response;
	}

}
