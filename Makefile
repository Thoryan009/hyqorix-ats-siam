# ===== Frontend Helpers =====

node-i:
	cd frontend && npm install

start:
	cd frontend && npm run dev

module:
	cd frontend && vue-modular make "${name}"

# ===== Backend Helpers =====

artisan:
	 cd backend && php artisan "${cmd}"

serve:
	cd backend && php artisan serve 

clear-a:
	 cd backend && php artisan optimize:clear

fresh:
	 cd backend && php artisan migrate:fresh --seed

link:
	 cd backend && php artisan storage:link

module-b:
	 cd backend && php artisan make:module "${name}"

sub-entity:
	cd backend && php artisan make:sub-entity "${parent}" "${child}"

update-c:
	cd backend && composer update

install:
	cd backend && php artisan module:install "${name}"

# ===== General Helpers =====
dev:

	make serve & make start