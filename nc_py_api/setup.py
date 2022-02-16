#!/usr/bin/env python
from setuptools import setup


def get_version():
    version_file = "nc_py_api/_version.py"
    with open(version_file) as f:
        exec(compile(f.read(), version_file, "exec"))
    return locals()["__version__"]


with open("README.md", "r", encoding="utf-8") as fh:
    long_description = fh.read()

with open("requirements.txt", "r", encoding="utf-8") as fh:
    install_requirements = [line.rstrip() for line in fh.readlines()]

setup(
    name="nc_py_api",
    version=get_version(),
    packages=["nc_py_api"],
    install_requires=[*install_requirements],
    author="Alexander Piskun, Andrey Borysenko",
    author_email="bigcat88@users.noreply.github.com",
    description="Python 3.7+ client interface for Nextcloud nc_py_api framework.",
    long_description=long_description,
    long_description_content_type="text/markdown",
    python_requires=">=3.7",
    classifiers=[
        "Programming Language :: Python :: 3",
        "License :: OSI Approved :: GNU Affero General Public License v3",
        "Operating System :: MacOS :: MacOS X",
        "Operating System :: POSIX :: Linux",
        "Operating System :: Microsoft :: Windows",
    ],
    license="AGPL-3.0",
    keywords="cloud_py_api nextcloud api",
    url="https://github.com/bigcat88/cloud_py_api",
    zip_safe=False,
)
