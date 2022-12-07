/**
 * @copyright Copyright (c) 2022-2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @copyright Copyright (c) 2022-2023 Alexander Piskun <bigcat88@icloud.com>
 *
 * @author 2022-2023 Andrey Borysenko <andrey18106x@gmail.com>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace php OCA.Cloud_Py_API.TProto
namespace py nc_py_api.TProto

/*
All communications messages send by client, server only replies on them.

Client must set TaskStatus and send TaskExit messages before exit.
Shutdown: server closes socket.
Remark: Both client and server part, must stop work after communication channel become broken.
*/

/* docs.nextcloud.com/server/latest/admin_manual/configuration_server/logging_configuration.html#log-level */
enum logLvl {
  DEBUG = 0,
  INFO = 1,
  WARN = 2,
  ERROR = 3,
  FATAL = 4
}

enum taskStatus {
  ST_SUCCESS = 0,
  ST_INITIALIZING = 1,                  // initialization is in progress
  ST_INSTALLING = 2,                    // install is in progress
  ST_INSTALL_ERROR = 3,                 // install return error
  ST_INIT_ERROR = 4,                    // error during initialization target app
  ST_IN_PROGRESS = 5,                   // target app is running
  ST_EXCEPTION = 6,                     // Unexpected exception occurred
  ST_UNKNOWN = 7                        // Default task state at start
}

enum taskType {
  T_DEFAULT = 0,                        // install silently if needed and run.
  T_CHECK = 1,                          // check app and returns it's info.
  T_INSTALL = 2,                        // install app and returns result of check.
  T_RUN = 3                             // only run, without any checks.
}

struct Empty { }

struct dbConfig {
  1:  string dbType,                    // DB Type configuration
  2:  string dbUser,                    // DB User configuration
  3:  string dbPass,                    // DB Password configuration
  4:  string dbHost,                    // DB Host configuration
  5:  string dbName,                    // DB Name configuration
  6:  string dbPrefix,                  // DB tables prefix configuration
  7:  string iniDbSocket,               // DB Socket configuration from ini (mysql)
  8:  string iniDbHost,                 // DB Host configuration from ini (mysql)
  9:  string iniDbPort,                 // DB Port configuration from ini (mysql)
  10: string dbDriverSslKey,            // DB MYSQL_ATTR_SSL_KEY configuration
  11: string dbDriverSslCert,           // DB MYSQL_ATTR_SSL_CERT configuration
  12: string dbDriverSslCa,             // DB MYSQL_ATTR_SSL_CA configuration
  13: string dbDriverSslVerifyCrt       // DB MYSQL_ATTR_SSL_VERIFY_SERVER_CERT configuration
}

struct cfgOptions {
  1: logLvl log_lvl,                    // 0-4 , level logs from NC documentation.
  2: string dataFolder,                 // Path to NC data folder.
  3: string userId,                     // Current NC user UID
  4: bool useFileDirect,                // Use extra direct FS module for python, when possible.
  5: bool useDBDirect,                  // Use extra direct DB module for python, if possible.
  6: i32 maxChunkSize,                  // Maximum chunk size of RPC stream data.
  7: i32 maxCreateFileContent           // Maximum chunk size for FS Create operations
}

struct TaskInitReply {
  1: taskType cmdType,
  2: string appName,                    // Name of the app. Installed packages will be stored by this name for app.
  3: string modPath,                    // Path to module root, to be executed.
  4: string funcName,                   // Which function to execute.
  5: list<string> taskArgs,                 // Optional arguments to pass to target python app's module.
  6: cfgOptions config,
  7: dbConfig dbCfg,                    // Database configuration
  8: string handler                     // PHP Callback handler for TaskExit
}

struct TaskSetStatusRequest {
  1: taskStatus st_code,                // Status code of a task.
  2: string error                       // Optional error, if any. Valid only when Status is non success/in_progress.
}

struct missing_pckg {
  1: string name,
  2: string version,                    // Format JSON: [["<=", "0.1.5"], [">=", "0.1.3"]]
}

struct installed_pckg {
  1: string name,
  2: string version,                    // Format JSON: "8.4.0"
  3: string location,                   // Path where it is installed.
  4: string summary,                    // Description, what this package do.
  5: string requires                    // Depends on. In JSON format, array of string.
}

struct CheckDataRequest {               // Framework returns this after TaskInitReply when cmdType != T_RUN.
  1: list<missing_pckg> not_installed,
  2: list<installed_pckg> installed
}

struct TaskExitRequest {                // No reply. Server must close pipe/socket after this message.
  1: string result,                     // Result of task, if any.
}

struct TaskLogRequest {                 // No reply.
  1: logLvl log_lvl,
  2: string logModule,                     // What module logs belongs to.
  3: list<string> content               // One or more strings to put to log.
}

struct OccRequest {
  1: list<string> arguments
}

struct OccReply {
  1: bool error,
  2: bool last,
  3: binary content,                    // If error=True, this field contains an error description.
}
