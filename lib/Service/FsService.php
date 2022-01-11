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
use OCA\Cloud_Py_API\Proto\FsMoveReply;
use OCA\Cloud_Py_API\Proto\FsMoveRequest;
use OCA\Cloud_Py_API\Proto\FsNodeInfo;
use OCA\Cloud_Py_API\Proto\FsReadReply;
use OCA\Cloud_Py_API\Proto\FsReadRequest;
use OCA\Cloud_Py_API\Proto\FsReply;
use OCA\Cloud_Py_API\Proto\fsResultCode;
use OCA\Cloud_Py_API\Proto\FsWriteRequest;


class FsService {

	public const CHUNK_SIZE = 5; // 4KB default chunk size

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
		$bytesToRead = $request->getBytesToRead();
		$userFolder = $this->rootFolder->getUserFolder($userId);
		$response = new FsReadReply();
		$response->setResCode(fsResultCode::NO_ERROR);
		$nodes = $userFolder->getById($fileId);
		if (count($nodes) === 1 && isset($nodes[0]) && $nodes[0] instanceof File) {
			/** @var File */
			$file = $nodes[0];
			$size = $file->getSize();
			try {
				$handle = $file->fopen('r');
				if ($handle) {
					if (isset($offset)) {
						if (fseek($handle, $offset) === -1) {
							$response->setResCode(fsResultCode::IO_ERROR);
							$writer->write($response);
							$writer->finish();
							return;
						}
					}
					if (isset($bytesToRead) && $bytesToRead !== 0) {
						$size = $bytesToRead;
					}
					$last = false;
					while (!$last) {
						$bytesLeft = $size - ftell($handle);
						$length = ($bytesLeft > self::CHUNK_SIZE) ? self::CHUNK_SIZE : $bytesLeft;
						$data = fread($handle, $length);
						$last = $bytesLeft <= self::CHUNK_SIZE;
						if (feof($handle) || $data === false) {
							$last = true;
						}
						if ($data !== false) {
							$response->setContent($data);
						}
						$response->setLast($last);
						if ($last) {
							break;
						}
						$writer->write($response);
					}
					fclose($handle);
				} else {
					$response->setResCode(fsResultCode::IO_ERROR);
				}
			} catch (NotPermittedException | LockedException $e) {
				if ($e instanceof NotPermittedException) {
					$response->setResCode(fsResultCode::NOT_PERMITTED);
				} else if ($e instanceof NotPermittedException) {
					$response->setResCode(fsResultCode::LOCKED);
				}
			}
		} else {
			$response->setResCode(fsResultCode::NOT_FOUND);
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
		if ($handle) {
			fclose($handle);
		}
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
		$folder = null;
		if (isset($parentDirId)) {
			$nodes = $userFolder->getById($parentDirId);
			if (count($nodes) === 1 && isset($nodes[0]) && $nodes[0] instanceof Folder) {
				/** @var Folder */
				$folder = $nodes[0];
			} else {
				$response->setResCode(fsResultCode::NOT_FOUND);
			}
		} else {
			$folder = $userFolder;
		}
		if (isset($folder)) {
			try {
				$newNode = null;
				if ($request->getIsFile()) {
					$newNode = $folder->newFile($request->getName(), $request->getContent());
				} else {
					$newNode = $folder->newFolder($request->getName());
				}
				if (isset($newNode)) {
					$fsId->setFileId($newNode->getId());
					$response->setFileId($fsId);
					$response->setResCode(fsResultCode::NO_ERROR);
				} else {
					$response->setResCode(fsResultCode::NOT_FOUND);
				}
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
	 * @return FsMoveReply FS Move results
	 */
	public function move(FsMoveRequest $request): ?FsMoveReply {
		$fsId = $request->getFileId();
		$userId = $fsId->getUserId();
		$nodeId = $fsId->getFileId();
		$userFolder = $this->rootFolder->getUserFolder($userId);
		$response = new FsMoveReply();
		$response->setResCode(fsResultCode::NO_ERROR);
		$fsId = new fsId();
		$nodes = $userFolder->getById($nodeId);
		if (count($nodes) === 1 && isset($nodes[0])) {
			/** @var Node */
			$node = $nodes[0];
			try {
				$newNode = null;
				if ($request->getCopy()) {
					$newNode = $node->copy($request->getTargetPath());
				} else {
					$newNode = $node->move($request->getTargetPath());
				}
				if (isset($newNode)) {
					$fsId->setFileId($newNode->getId());
					$fsId->setUserId($newNode->getOwner()->getUID());
				}
			} catch (NotPermittedException | NotFoundException | LockedException | InvalidPathException $e) {
				if ($e instanceof NotPermittedException) {
					$response->setResCode(fsResultCode::NOT_PERMITTED);
				} else if ($e instanceof NotFoundException || $e instanceof InvalidPathException) {
					$response->setResCode(fsResultCode::NOT_FOUND);
				} else if ($e instanceof LockedException) {
					$response->setResCode(fsResultCode::LOCKED);
				}
			}
		} else {
			$response->setResCode(fsResultCode::NOT_FOUND);
		}
		$response->setFileId($fsId);
		return $response;
	}

}
