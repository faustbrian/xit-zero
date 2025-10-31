compose_command = docker-compose run -u $(id -u ${USER}):$(id -g ${USER}) --rm php84

docker-build:
	docker-compose build

shell: docker-build
	$(compose_command) bash

destroy:
	docker-compose down -v

composer: docker-build
	$(compose_command) composer install

lint: docker-build
	$(compose_command) composer lint

test: docker-build
	$(compose_command) composer test

test\:lint: docker-build
	$(compose_command) composer test:lint

test\:unit: docker-build
	$(compose_command) composer test:unit

# Laravel Zero PHAR & Binary Distribution
phar: docker-build
	$(compose_command) php xit app:build xit.phar

binary: phar
	$(compose_command) ./vendor/bin/phpacker build --src=./builds/xit.phar --php=8.4 all

binary-mac: phar
	$(compose_command) ./vendor/bin/phpacker build --src=./builds/xit.phar --php=8.4 mac

binary-linux: phar
	$(compose_command) ./vendor/bin/phpacker build --src=./builds/xit.phar --php=8.4 linux

binary-windows: phar
	$(compose_command) ./vendor/bin/phpacker build --src=./builds/xit.phar --php=8.4 windows

run-mac-arm: binary
	./builds/build/mac/mac-arm

run-mac-x64: binary
	./builds/build/mac/mac-x64

run-linux-arm: binary
	./builds/build/linux/linux-arm

run-linux-x64: binary
	./builds/build/linux/linux-x64

run-windows: binary
	./builds/build/windows/windows-x64.exe

clean-builds:
	rm -rf ./builds/*

# Release Management
release-patch:
	@CURRENT=$$(git describe --tags --abbrev=0 2>/dev/null | sed 's/^v//' || echo "1.0.0"); \
	NEW=$$(echo $$CURRENT | awk -F. '{print $$1"."$$2"."$$3+1}'); \
	echo "Bumping $$CURRENT → $$NEW"; \
	git add -A; \
	git commit -m "chore: release $$NEW" || true; \
	git push origin main; \
	git tag -a $$NEW -m "Release $$NEW"; \
	git push origin $$NEW; \
	gh release create $$NEW --generate-notes; \
	echo "Released $$NEW"

release-minor:
	@CURRENT=$$(git describe --tags --abbrev=0 2>/dev/null | sed 's/^v//' || echo "1.0.0"); \
	NEW=$$(echo $$CURRENT | awk -F. '{print $$1"."$$2+1".0"}'); \
	echo "Bumping $$CURRENT → $$NEW"; \
	git add -A; \
	git commit -m "chore: release $$NEW" || true; \
	git push origin main; \
	git tag -a $$NEW -m "Release $$NEW"; \
	git push origin $$NEW; \
	gh release create $$NEW --generate-notes; \
	echo "Released $$NEW"

release-major:
	@CURRENT=$$(git describe --tags --abbrev=0 2>/dev/null | sed 's/^v//' || echo "1.0.0"); \
	NEW=$$(echo $$CURRENT | awk -F. '{print $$1+1".0.0"}'); \
	echo "Bumping $$CURRENT → $$NEW"; \
	git add -A; \
	git commit -m "chore: release $$NEW" || true; \
	git push origin main; \
	git tag -a $$NEW -m "Release $$NEW"; \
	git push origin $$NEW; \
	gh release create $$NEW --generate-notes; \
	echo "Released $$NEW"

.PHONY: docker-build build phar binary binary-mac binary-linux binary-windows \
	run-mac-arm run-mac-x64 run-linux-arm run-linux-x64 run-windows clean-builds \
	release-patch release-minor release-major
