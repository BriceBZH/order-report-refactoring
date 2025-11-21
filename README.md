# order-report-refactoring

## Installation

### Prérequis
- [Langage] version X.X
- [Gestionnaire] version Y.Y

### Commandes
```bash
composer install
```
# Commandes pour installer les dépendances


#### **2. Exécution**
```markdown
## Exécution

### Exécuter le code refactoré
```bash
php src/orderReportLegacy.php
```
# Commande pour lancer votre code

# Commande pour lancer tous les tests

```bash
php tests/goldenMasterTest.php
```
# Commande pour comparer les sorties

#### **3. Choix de Refactoring**

Expliquez vos **décisions principales** :

```markdown
## Choix de Refactoring

### Problèmes Identifiés dans le Legacy

1. **Extract CSV parsing** : l'extract des différents contenus des fichiers CSV existe sous différentes forme et plusieurs fois
   - Impact : c'est problématique dans le sens où si on veut faire un changement il fallait faire plusieurs modifications.

2. **models for entities** : aucune classe utilisée, il n'y a que des tableaux associatifs
   - Impact : Difficile de s'y retrouver facilement, les erreurs sont trop faciles.

3. **Const variables** : présence de variables globale dans le fichier de l'application
   - Impact : Pas très clair de les mettres directement dans le fichier principal

### Solutions Apportées

1. **Extract CSV parsing** : l'extract des différents contenus des fichiers CSV, fait en 2 temps, ajout d'une function unique pour parcourrir un fichier puis ajout d'une fonction par fichier pour la "mise en forme"
   - Justification : ce n'était pas tout à fait une duplication car parfois le code pour l'extraction était différent, mais le résultat était le même, c'est problématique dans le sens où si on veut faire un changement il fallait faire plusieurs modifications.

2. **models for entities** : utilisation de classe pour créer des entité (en fonction des différents fichiers CSV)
   - Justification : Il y a moins de risque de faire des erreurs "invisibles" avec des objets plutôt qu'avec des tableaux, il y a plus de clarté car on retrouver facilement le contenu de notre objet avec sa définition.

3. **Const variables** : extraction de ces variables dans un fichier de configuration
   - Justification : Plus clair de les mettres en dehors du fichier principal, donc j'ai fait le choix de les mettres dans un fhicer à part pour plus de clarté

### Architecture Choisie

[Décrivez brièvement comment vous avez organisé votre code]
- Modules/packages créés
- Rôle de chaque module
- Flux de données

### Exemples Concrets

**Exemple 1 : [Nom du refactoring]**
- Problème : [code smell spécifique]
- Solution : [approche retenue]

**Exemple 2 : [Autre refactoring]**
- ...

## Limites et Améliorations Futures

### Erreurs survenues après le refactoring

Suite au passage à l'utilisation d'entité (plus exactement Order), plusieurs erreurs de montant sont apparues, debug commencé mais code trop inconnue/mal rangé pour découvrir le problème... 

### Ce qui n'a pas été fait (par manque de temps)
- [Extraction des différents calculs de remises] [Amélioration souhaitée]
- [Extraction des calculs de frais de port] [Amélioration souhaitée]
- [Extraction des calculs de taxes] [Amélioration souhaitée]
- [Extraction des calculs du weekend] [Amélioration souhaitée]
- [Extraction de la partie formatage] [Amélioration souhaitée]

### Compromis Assumés
- [Compromis 1] : [justification]
- [Compromis 2] : [justification]

### Pistes d'Amélioration Future
- [Idée 1]
- [Idée 2]