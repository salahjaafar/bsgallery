# Plugin BSGallery - Galerie Bootstrap 5 pour Joomla

## Description

Le plugin **BSGallery** transforme la balise `{bsgallery} dossier_images {/bsgallery}` en une galerie d'images responsive avec Bootstrap 5, incluant une visionneuse modale avec navigation (précédent/suivant) et support du clavier.

## Fonctionnalités

- **Galerie responsive** : Utilise le système de grille Bootstrap 5
- **Visionneuse modale** : Affiche les images en grand dans une modale Bootstrap 5
- **Navigation** : Boutons précédent/suivant et navigation au clavier (flèches + Escape)
- **Sécurité** : Vérification des chemins pour empêcher l'accès à des dossiers non autorisés
- **IDs uniques** : Supporte plusieurs galeries sur la même page
- **Formats supportés** : jpg, jpeg, png, webp, gif

## Installation

1. Zippez le contenu du dossier `plg_content_bsgallery`
2. Connectez-vous à l'administration Joomla
3. Allez dans **Système > Installer > Extensions**
4. Sélectionnez le fichier ZIP et cliquez sur "Installer"

## Activation

1. Allez dans **Système > Gérer > Plugins**
2. Recherchez "BSGallery" ou "plg_content_bsgallery"
3. Activez le plugin

## Configuration

Dans les paramètres du plugin, vous pouvez définir le nombre de colonnes de la grille :

| Valeur | Description |
|--------|-------------|
| 2 | 2 colonnes (très large) |
| 3 | 3 colonnes |
| 4 | 4 colonnes (défaut) |
| 6 | 6 colonnes (compact) |

## Utilisation

### Syntaxe de base

Dans n'importe quel article Joomla, utilisez la balise suivante :

```
{bsgallery}nom_du_dossier{/bsgallery}
```

### Exemple

Vos images doivent être placées dans le dossier `/images/` de Joomla.

Structure recommandée :

```
/images/
  └── ma-galerie/
      ├── photo1.jpg
      ├── photo2.jpg
      └── photo3.png
```

Dans votre article :

```
{bsgallery}ma-galerie{/bsgallery}
```

### Notes importantes

- Le chemin est toujours relatif au dossier `/images/` de Joomla
- Le dossier doit exister et contenir des images valides
- Les extensions autorisées sont : jpg, jpeg, png, webp, gif

## Exemples complets

### Galerie de vacances

```
{bsgallery}vacances-2024{/bsgallery}
```

### Portfolio

```
{bsgallery}portfolio/photos{/bsgallery}
```

### Produits

```
{bsgallery}produits/bijoux{/bsgallery}
```

## Personnalisation CSS

Le plugin utilise les classes Bootstrap 5 suivantes :

| Élément | Classe |
|---------|--------|
| Conteneur | `container-fluid p-0 mb-4` |
| Grille | `row g-3` |
| Colonne | `col-6 col-md-{n}` |
| Image miniature | `img-fluid rounded shadow-sm bsgallery-thumb` |
| Modale | `modal fade` |
| Image agrandie | `img-fluid rounded shadow-lg` |

Pour personnaliser, ajoutez du CSS dans votre template ou article.

### Exemple de personnalisation

```css
.bsgallery-thumb {
    transition: transform 0.3s ease;
}
.bsgallery-thumb:hover {
    transform: scale(1.05);
}
```

## Navigation dans la modale

| Action | Méthode |
|--------|---------|
| Image précédente | Cliquer sur le bouton `<<` ou touche `Flèche gauche` |
| Image suivante | Cliquer sur le bouton `>>` ou touche `Flèche droite` |
| Fermer | Cliquer sur `X`, cliquer à l'extérieur de l'image, ou touche `Escape` |

## Messages d'erreur

| Message | Cause |
|---------|-------|
| `Dossier introuvable : images/...` | Le dossier n'existe pas dans `/images/` |
| `Accès non autorisé au dossier.` | Tentative d'accès hors du dossier `/images/` |
| `Aucune image valide trouvée` | Le dossier existe mais ne contient pas d'images valides |

## Structure du plugin

```
plg_content_bsgallery/
├── bsgallery.xml          # Manifeste XML du plugin
├── services/
│   └── provider.php       # Fournisseur de services DI
└── src/
    └── Extension/
        └── Bsgallery.php  # Classe principale du plugin
```

## Compatibilité

- **Joomla** : 5.x / 6.x
- **PHP** : 8.1+
- **Bootstrap** : 5.x (inclus dans le template Joomla)

## Support

Pour signaler un bug ou demander une fonctionnalité, contactez l'auteur.
