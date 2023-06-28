# Nextcloud Python Framework

[![(Py)Analysis & Coverage](https://github.com/cloud-py-api/cloud_py_api/actions/workflows/py_analysis-coverage.yml/badge.svg)](https://github.com/cloud-py-api/cloud_py_api/actions/workflows/py_analysis-coverage.yml)
![PythonVersion](https://img.shields.io/badge/python-3.9%20%7C%203.10%20%7C%203.11-blue)
![impl](https://img.shields.io/pypi/implementation/nc_py_api)
![pypi](https://img.shields.io/pypi/v/nc_py_api.svg)
[![codecov](https://codecov.io/gh/cloud-py-api/cloud_py_api/branch/main/graph/badge.svg?token=6IHPKUYUU9)](https://codecov.io/gh/cloud-py-api/cloud_py_api)

Framework(App) for Nextcloud to develop apps, that using Python.

Consists of PHP part(**cloud_py_api app**) and a Python module(**nc-py-api**).

## Current state: Abandoned
### Project was divided into two different repositories:
### https://github.com/cloud-py-api/app_ecosystem_v2
### https://github.com/cloud-py-api/nc_py_api

## Provides Convenient Functions for Python

- Read & Write File System objects
- Working with Database
- Wrapper around `OCC` calls
- Calling your python function from php part of app and return a result

## 🚀 Installation

In your Nextcloud, simply enable the `cloud_py_api` app through the Apps management and then install apps, that using it.

The Nextcloud `cloud_py_api` app supports Nextcloud version 25 and higher.

#### More information can be found on [Wiki page](https://github.com/cloud-py-api/cloud_py_api/wiki)

## Maintainers

* [Andrey Borysenko](https://github.com/andrey18106)
* [Alexander Piskun](https://github.com/bigcat88)

## Apps using this

- [MediaDC](https://github.com/andrey18106/mediadc) - Nextcloud Media Duplicate collector app. Python part - core logics for duplicates search.
