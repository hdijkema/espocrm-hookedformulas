
all: ext
	@echo "Extension built, you should be able to install it using EspoCRM extension installer"

ext:
	bash ./buildext

clean:
	bash ./build-hookedformulas.sh cleanup

install:
	bash ./build-hookedformulas.sh install

uninstall:
	bash ./build-hookedformulas.sh uninstall

espocrm6_install:

	
