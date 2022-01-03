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
use OCP\Files\IRootFolder;

use Cloud_Py_API\FsCreateRequest;
use Cloud_Py_API\FsDeleteRequest;
use Cloud_Py_API\FsListReply;
use Cloud_Py_API\FsListRequest;
use Cloud_Py_API\FsMoveRequest;
use Cloud_Py_API\FsReadReply;
use Cloud_Py_API\FsReadRequest;
use Cloud_Py_API\FsReply;
use Cloud_Py_API\FsWriteRequest;


class FsService {

	/** @var IAppData */
	private $appData;

	/** @var IRootFolder */
	private $rootFolder;

	public function __construct(IAppData $appData, IRootFolder $rootFolder)
	{
		$this->appData = $appData;
		$this->rootFolder = $rootFolder;
	}

	/**
	 * FS List directory
	 * 
	 * @param FsListRequest $request
	 * 
	 * @return FsListReply|null FS List directory result
	 */
	public function list(FsListRequest $request): FsListReply {
		return new FsListReply(null);
	}

	/**
	 * FS Read file
	 * 
	 * @param FsReadRequest $request
	 * 
	 * @return FsReadReply|null FS Read file contents
	 */
	public function read(FsReadRequest $request): FsReadReply {
		return new FsReadReply(null);
	}

	/**
	 * FS Write file
	 * 
	 * @param FsWriteRequest $request
	 * 
	 * @return FsReply|null FS Write file results
	 */
	public function write(FsWriteRequest $request): FsReply {
		return new FsReply(null);
	}

	/**
	 * FS Create method
	 * 
	 * @param FsCreateRequest $params
	 * 
	 * @return FsReply|null FS Create results
	 */
	public function create(FsCreateRequest $request): FsReply {
		return new FsReply(null);
	}

	/**
	 * FS Delete method
	 * 
	 * @param FsDeleteRequest $request
	 * 
	 * @return FsReply|null FS Delete results
	 */
	public function delete(FsDeleteRequest $request): FsReply {
		return new FsReply(null);
	}

	/**
	 * FS Move method
	 * 
	 * @param FsMoveRequest $request
	 * 
	 * @return FsReply FS Move results
	 */
	public function move(FsMoveRequest $request): FsReply {
		return new FsReply(null);
	}

}