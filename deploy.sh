#!/bin/bash

B='\e[1m'
RED='\e[31m'
GREEN='\e[32m'
CYAN='\e[36m'
NC='\e[0m' # No Color

DOCKER_ECR_REPO_URL="005279544259.dkr.ecr.us-west-2.amazonaws.com"

REVISION=$(git rev-parse --short HEAD)
BRANCH=$(git status | head -n 1 | awk '{print $3}')
BRANCH=${BRANCH//"/"/"-"}
DEPLOY_NAME=$(basename $(git remote show -n origin | grep URL | head -1 | cut -d: -f2-) | awk -F '.' '{print $1}')

if [ -z "$2" ]; then
  ENVIRONMENT="local"
  ENV_FILE=""
else
  ENVIRONMENT=$2
  ENV_FILE="."$2
fi

DOCKER_IMAGE=$DOCKER_ECR_REPO_URL/$DEPLOY_NAME-$ENVIRONMENT

case "$1" in
build)
  echo ""
  echo -e "${B}${GREEN}1. ### UPDATE GIT SUBMODULE ###${NC}"
  git submodule init
  git submodule update --remote --force

  echo -e "${B}${GREEN}2. ### START BUILD ###${NC}"
  echo -e "${CYAN}Deploy Name: $DEPLOY_NAME${NC}"
  echo -e "${CYAN}Branch: $BRANCH${NC}"
  echo -e "${CYAN}Rev: $REVISION${NC}"
  echo -e "${CYAN}Environment: $ENVIRONMENT${NC}"

  docker build -f Dockerfile --build-arg env_file="$ENV_FILE" -t $DOCKER_IMAGE:$BRANCH-$REVISION -t $DOCKER_IMAGE:latest .
  ;;
push)
  echo ""
  echo -e "${B}${GREEN}### PUSH DOCKER IMAGE BUILD ###${NC}"
  echo -e "${CYAN}Deploy Name: $DEPLOY_NAME${NC}"
  echo -e "${CYAN}Branch: $BRANCH${NC}"
  echo -e "${CYAN}Rev: $REVISION${NC}"
  echo -e "${CYAN}Environment: $ENVIRONMENT${NC}"

  docker push $DOCKER_IMAGE:$BRANCH-$REVISION
  docker push $DOCKER_IMAGE:latest
  ;;
start)
  echo ""
  echo -e "${B}${GREEN}1. ### GENERATE DOCKER-COMPOSE ###${NC}"
  echo -e "${CYAN}Deploy Name: $DEPLOY_NAME${NC}"
  echo -e "${CYAN}Branch: $BRANCH${NC}"
  echo -e "${CYAN}Rev: $REVISION${NC}"
  echo -e "${CYAN}Environment: $ENVIRONMENT${NC}"

  cat compose-tmpl.yaml | grep -v "#" >docker-compose.yaml
  sed -i"" "s~{{DEPLOY_NAME}}~$DEPLOY_NAME~" docker-compose.yaml
  sed -i"" "s~{{DOCKER_IMAGE}}~$DOCKER_IMAGE:$BRANCH-$REVISION~" docker-compose.yaml

  echo -e "${B}${GREEN}2. ### RUN DOCKER CONTAINER ###${NC}"
  docker-compose stop
  docker-compose up -d
  ;;
stop)
  echo ""
  echo -e "${B}${GREEN}### STOP DOCKER CONTAINER ###${NC}\n"
  docker-compose stop
  ;;
login)
  echo ""
  echo -e "${B}${GREEN}### LOGIN TO AWS ELASTIC CONTAINER REGISTRY ###${NC}\n"
  echo $(aws ecr get-login-password --region us-west-2 | docker login \
      --username AWS \
      --password-stdin ${DOCKER_ECR_REPO_URL}) # > login.sh
  #sh login.sh
  #rm login.sh
  ;;
rm)
  echo ""
  echo -e "${B}${GREEN}### REMOVE DOCKER CONTAINER ###${NC}\n"
  docker-compose rm -f
  ;;
*)
  echo ""
  echo -e "${B}${RED}Command $1 is not implemented${NC}\n"
  ;;
esac
