
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
	cp EspoCRM6/LogAddType.php ../../../application/Espo/Modules/HookedFormulas/Core/Formula/Functions/
	cp EspoCRM6/ConfigSetType.php ../../../application/Espo/Modules/HookedFormulas/Core/Formula/Functions/


	
