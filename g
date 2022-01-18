#!/bin/bash
#faster git workflow by sferics (R)

git status
git add -u
git commit -m "$1"
git push
