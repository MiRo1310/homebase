import { Application } from '@hotwired/stimulus';
import HelloController from './controllers/hello_controller.js';

// stimulus-bundle's own loader is built for AssetMapper (resolves controllers.json
// via a Composer/vendor asset) and isn't resolvable through Webpack - controllers are
// registered directly here instead.
const app = Application.start();
app.register('hello', HelloController);
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);

export default app;
