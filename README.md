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

1. **Extract CSV parsing** : l'extract des différents contenus des fichiers CSV, fait en 2 temps, ajout d'une function unique pour parcourrir un fichier puis ajout d'une fonction par fichier pour la "mise en forme"
   - Impact : ce n'était pas tout à fait une duplication car parfois le code pour l'extraction était différent, mais le résultat était le même, c'est problématique dans le sens où si on veut faire un changement il fallait faire plusieurs modifications.

2. **models for entities** : utilisation de classe pour créer des entité (en fonction des différents fichiers CSV)
   - Impact : [...]

3. **models for entities** : utilisation de classe pour créer des entité (en fonction des différents fichiers CSV)
   - Impact : [...]

### Solutions Apportées

1. **[Amélioration 1]** : [Ce que vous avez fait]
   - Justification : [pourquoi ce choix]

2. **[Amélioration 2]** : [Ce que vous avez fait]
   - Justification : [...]

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

### Ce qui n'a pas été fait (par manque de temps)
- [ ] [Amélioration souhaitée]
- [ ] [Autre amélioration]

### Compromis Assumés
- [Compromis 1] : [justification]
- [Compromis 2] : [justification]

### Pistes d'Amélioration Future
- [Idée 1]
- [Idée 2]