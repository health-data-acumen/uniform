import { Controller } from '@hotwired/stimulus';
import Prism from 'prismjs';
import 'prismjs/themes/prism-okaidia.min.css';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  connect() {
    Prism.highlightAll();
  }
}
