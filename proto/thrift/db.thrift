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

enum exprType {
  EQ = 0,
  NEQ = 1,
  LT = 2,
  LTE = 3,
  GT = 4,
  GTE = 5,
  IS_NULL = 6,
  IS_NOT_NULL = 7,
  LIKE = 8,
  NOT_LINE = 9,
  IN = 10,
  NOT_IN = 11
}

enum pType {
  DEFAULT = 0,
  NAMED = 1,
  POSITIONAL = 2,
}

enum pValueType {
  NULL = 0,
  BOOL = 1,
  INT = 2,
  STR = 3,
  LOB = 4,
  DATE = 5,
  INT_ARRAY = 6,
  STR_ARRAY = 7
}

struct whereExpr {                      // struct for `where` part.
  1: string type,                       // where, andWhere, orWhere
  2: string expression,
  3: exprType expressionType,
  4: pType paramType,
  5: string paramColumn,
  6: string paramValue,
  7: pValueType paramValueType
}

struct str_alias {
  1: string name,
  2: string stralias
}

struct joinType {
  1: string name,                    // join, innerJoin, leftJoin, rightJoin.
  2: string fromAlias,
  3: string join,
  4: string stralias,
  5: string condition
}

struct havingExpr {
  1: string type,                       // having, andHaving, orHaving
  2: string expression
}

struct DbSelectRequest {
  1: list<str_alias> columns,           // aliases or filed names.
  2: list<str_alias> strfrom,
  3: list<joinType> joins,
  4: list<whereExpr> whereas,
  5: list<string> groupBy,
  6: list<havingExpr> havings,
  7: list<string> orderBy,
  8: i64 maxResults,
  9: i64 firstResult
}

struct DbSelectReply {
  1: i64 rowCount,
  2: string error,
  3: string handle                      // valid if only rowcount > 0.
}

enum cCmd {
  FETCH = 0,
  FETCH_ALL = 1,
  CLOSE = 2
}

struct DbCursorRequest {
  1: cCmd cmd,
  2: string handle
}

struct columnData {
  1: bool present,                   // if result is NULL for that column raw, then this is True.
  2: binary data                     // present if result fir column raw is not NULL.
}
struct DbCursorReply {
  1: string error,
  2: list<string> columnsName,
  3: list<columnData> columnsData
}

enum rType {
  INSERT = 0,
  UPDATE = 1,
  DELETE = 2
}

struct DbExecRequest {
  1: rType type,
  2: string table_name,
  3: list<string> columns,
  4: list<binary> values,
  5: list<whereExpr> whereas,
}

struct DbExecReply {
  1: i64 nAffectedRows,
  2: i64 lastInsertId,
  3: string error
}