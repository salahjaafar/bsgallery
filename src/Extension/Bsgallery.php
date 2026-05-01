<?php
/**
 * Plugin de contenu Simple Bootstrap Gallery pour Joomla 6
 * Avec Modal Adaptatif, Navigation (Suivant/Précédent) et Fermeture.
 */

namespace Joomla\Plugin\Content\Bsgallery\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

class Bsgallery extends CMSPlugin implements SubscriberInterface
{
    /**
     * Sécurité : Extensions d'images autorisées.
     */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /**
     * Pour s'assurer de n'injecter le code du Modal qu'une seule fois par page.
     */
    private static bool $modalInjected = false;

    /**
     * Compteur pour générer des IDs uniques si plusieurs galeries sont sur la même page.
     */
    private static int $galleryCount = 0;

    /**
     * Méthode requise par SubscriberInterface.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepare' => 'onContentPrepare',
        ];
    }

    /**
     * Événement principal déclenché par Joomla.
     */
    public function onContentPrepare(\Joomla\CMS\Event\Content\ContentPrepareEvent $event): void
    {
        $context = $event->getContext();
        $item    = $event->getItem();

        if ($context !== 'com_content.article' || empty($item->text)) {
            return;
        }

        $text = &$item->text;
        $pattern = '/\{bsgallery\}(.*?)\{\/bsgallery\}/i';

        if (!preg_match($pattern, $text)) {
            return;
        }

        $text = preg_replace_callback($pattern, [$this, 'renderGallery'], $text);
    }

    /**
     * Génère le code HTML de la galerie.
     */
    private function renderGallery(array $match): string
    {
        $folderPath = trim($match[1]);
        $absolutePath = JPATH_ROOT . '/images/' . $folderPath;
        
        if (!realpath($absolutePath) || !is_dir($absolutePath)) {
            return '<div class="alert alert-warning">Dossier introuvable : images/' . htmlspecialchars($folderPath) . '</div>';
        }

        $realPath = realpath($absolutePath);
        $imagesRoot = realpath(JPATH_ROOT . '/images');
        if (strpos($realPath, $imagesRoot) !== 0) {
            return '<div class="alert alert-danger">Accès non autorisé au dossier.</div>';
        }

        $images = $this->getImagesFromFolder($realPath, $folderPath);

        if (empty($images)) {
            return '<div class="alert alert-info">Aucune image valide trouvée dans : images/' . htmlspecialchars($folderPath) . '</div>';
        }

        $columns = (int) $this->params->get('columns', 4);
        $colClass = 12 / $columns;
        
        // ID unique pour cette galerie spécifique (utile pour le JS)
        self::$galleryCount++;
        $galleryId = 'bsgallery-row-' . self::$galleryCount;

        $html = '<div class="container-fluid p-0 mb-4">';
        $html .= '<div class="row g-3" id="' . $galleryId . '">'; // ID ajouté ici

        foreach ($images as $image) {
            $html .= '<div class="col-6 col-md-' . $colClass . '">';
            $html .= '  <img src="' . $image['url'] . '" '
                     . 'class="img-fluid rounded shadow-sm bsgallery-thumb" '
                     . 'data-bs-toggle="modal" data-bs-target="#bsgalleryModal" '
                     . 'data-bs-img="' . $image['url'] . '" '
                     . 'alt="' . htmlspecialchars($image['name']) . '" '
                     . 'style="cursor: pointer; width: 100%; height: 200px; object-fit: cover;">';
            $html .= '</div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        $html .= $this->getModalHtml();

        return $html;
    }

    /**
     * Scanne le dossier et filtre les fichiers.
     */
    private function getImagesFromFolder(string $absolutePath, string $relativePath): array
    {
        $images = [];
        $files = scandir($absolutePath);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            
            if (in_array($extension, self::ALLOWED_EXTENSIONS)) {
                $images[] = [
                    'name' => pathinfo($file, PATHINFO_FILENAME),
                    'url'  => \Joomla\CMS\Uri\Uri::root(true) . '/images/' . $relativePath . '/' . $file
                ];
            }
        }
        return $images;
    }

    /**
     * Génère le HTML du Modal et le JavaScript Vanilla de navigation.
     */
    private function getModalHtml(): string
    {
        if (self::$modalInjected) {
            return '';
        }
        self::$modalInjected = true;

        $modalHtml = '
        <!-- Simple Bootstrap Gallery Modal -->
        <div class="modal fade" id="bsgalleryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content bg-transparent border-0">
                    <div class="modal-body p-0 position-relative d-flex justify-content-center align-items-center" style="min-height: 50vh;">
                        
                        <!-- Bouton Fermer (Croix) -->
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        
                        <!-- Bouton Précédent -->
                        <button type="button" class="btn btn-dark position-absolute start-0 top-50 translate-middle-y ms-2 z-3 rounded-circle p-2" id="bsgalleryPrevBtn" aria-label="Image précédente" style="width: 40px; height: 40px;">
                            <span aria-hidden="true">&laquo;</span>
                        </button>
                        
                        <!-- Image du Modal -->
                        <img src="" class="img-fluid rounded shadow-lg" id="bsgalleryModalImg" alt="Image agrandie" style="max-height: 85vh; object-fit: contain;">
                        
                        <!-- Bouton Suivant -->
                        <button type="button" class="btn btn-dark position-absolute end-0 top-50 translate-middle-y me-2 z-3 rounded-circle p-2" id="bsgalleryNextBtn" aria-label="Image suivante" style="width: 40px; height: 40px;">
                            <span aria-hidden="true">&raquo;</</span>
                        </button>

                    </div>
                </div>
            </div>
        </div>';

        // JavaScript Vanilla (ES6) pour la navigation et la fermeture
        $js = "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var bsgModal = document.getElementById('bsgalleryModal');
                if (!bsgModal) return;

                var modalImage = document.getElementById('bsgalleryModalImg');
                var prevBtn = document.getElementById('bsgalleryPrevBtn');
                var nextBtn = document.getElementById('bsgalleryNextBtn');
                
                var currentImages = [];
                var currentIndex = 0;

                // 1. À l'ouverture du Modal : on récupère les images de la galerie cliquée
                bsgModal.addEventListener('show.bs.modal', function (event) {
                    var triggerElement = event.relatedTarget; // L'image cliquée
                    
                    // On remonte à la row de la galerie pour trouver toutes les images du même dossier
                    var galleryRow = triggerElement.closest('.row');
                    if (galleryRow) {
                        var thumbs = galleryRow.querySelectorAll('.bsgallery-thumb');
                        currentImages = Array.from(thumbs).map(thumb => thumb.getAttribute('data-bs-img'));
                        currentIndex = currentImages.indexOf(triggerElement.getAttribute('data-bs-img'));
                    }
                    updateModalImage();
                });

                // 2. Mise à jour de l'image et des boutons de navigation
                function updateModalImage() {
                    if (currentIndex >= 0 && currentIndex < currentImages.length) {
                        modalImage.src = currentImages[currentIndex];
                    }
                }

                // 3. Clic sur Précédent
                prevBtn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Empêche la fermeture du modal
                    currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
                    updateModalImage();
                });

                // 4. Clic sur Suivant
                nextBtn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Empêche la fermeture du modal
                    currentIndex = (currentIndex + 1) % currentImages.length;
                    updateModalImage();
                });

                // 5. Navigation au clavier (Flèches et Echap)
                document.addEventListener('keydown', function(e) {
                    // On n'agit que si le modal est ouvert
                    if (!bsgModal.classList.contains('show')) return;

                    if (e.key === 'ArrowLeft') {
                        prevBtn.click();
                    } else if (e.key === 'ArrowRight') {
                        nextBtn.click();
                    } else if (e.key === 'Escape') {
                        // Bootstrap gère déjà ESC, mais on peut forcer la fermeture proprement
                        var modalInstance = bootstrap.Modal.getInstance(bsgModal);
                        if (modalInstance) modalInstance.hide();
                    }
                });

                // 6. Clic en dehors de l'image pour fermer
                modalImage.addEventListener('click', function(e) {
                    e.stopPropagation(); // Le clic sur l'image ne fait rien (ne ferme pas)
                });
                
                // Le clic sur le fond noir (modal-body) ferme le modal
                bsgModal.querySelector('.modal-body').addEventListener('click', function(e) {
                    if (e.target === this) {
                        var modalInstance = bootstrap.Modal.getInstance(bsgModal);
                        if (modalInstance) modalInstance.hide();
                    }
                });
            });
        </script>";

        return $modalHtml . $js;
    }
}
