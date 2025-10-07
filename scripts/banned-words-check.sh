#!/bin/bash
# Banned-ordsjekk: Ingen mock/"kommer snart" tillatt I KODE
# Fase 0.2 - AI-learned kontroll
# OBS: Bannede ord er lov i beskrivelser/dokumentasjon, men ikke i kode

BANNED_WORDS=("kommer snart" "her kommer" "TODO" "lorem" "mock data" "FIXME" "XXX" "will be added" "not implemented")
PROJECT_ROOT="/var/www/vhosts/smartesider.no/nytt.smartesider.no"
EXCLUDE_DIRS=("vendor" "node_modules" "storage" "bootstrap/cache" "AI-learned" "scripts" "docs" "README.md")
EXCLUDE_FILES=("database/factories" "config/app.php" "config/logging.php")

# Farger for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "=== BANNED-ORD SJEKK (kun kode-filer) ==="
echo "Kjøretid: $(date -Iseconds)"
echo "Ekskluderer: dokumentasjon, AI-learned, scripts"

FOUND_COUNT=0
TOTAL_FILES=0

# Bygg exclude-pattern for grep
EXCLUDE_PATTERN=""
for dir in "${EXCLUDE_DIRS[@]}"; do
    EXCLUDE_PATTERN="$EXCLUDE_PATTERN --exclude-dir=$dir"
done

# Ekskluder også spesifikke filer
for file_pattern in "${EXCLUDE_FILES[@]}"; do
    EXCLUDE_PATTERN="$EXCLUDE_PATTERN --exclude=$file_pattern/*"
done

# Søk etter hvert banned ord
for word in "${BANNED_WORDS[@]}"; do
    echo ""
    echo "Søker etter: '$word'"
    
    RESULT=$(grep -rni $EXCLUDE_PATTERN \
        --include="*.php" \
        --include="*.blade.php" \
        --include="*.js" \
        --include="*.vue" \
        --exclude="*.md" \
        --exclude="*.txt" \
        --exclude="*.json" \
        --exclude="*.lock" \
        "$word" "$PROJECT_ROOT" 2>/dev/null || true)
    
    if [ ! -z "$RESULT" ]; then
        echo -e "${RED}✗ FEIL: Fant '$word' i følgende filer:${NC}"
        echo "$RESULT"
        FOUND_COUNT=$((FOUND_COUNT + 1))
    else
        echo -e "${GREEN}✓ OK: Ingen treff for '$word'${NC}"
    fi
done

echo ""
echo "=== RESULTAT ==="

if [ $FOUND_COUNT -eq 0 ]; then
    echo -e "${GREEN}✓ BESTÅTT: Ingen banned ord funnet!${NC}"
    
    # Logg til donetoday.json
    TIMESTAMP=$(date -Iseconds)
    TEMP_FILE=$(mktemp)
    
    jq --arg ts "$TIMESTAMP" \
       '.aktiviteter += [{
           "timestamp": $ts,
           "fase": "0.2",
           "beskrivelse": "Banned-ord sjekk bestått (kun kode-filer)",
           "filer": ["alle .php, .blade.php, .js, .vue filer"],
           "resultat": "OK - ingen banned ord funnet i kode"
       }]' "$PROJECT_ROOT/AI-learned/donetoday.json" > "$TEMP_FILE"
    
    mv "$TEMP_FILE" "$PROJECT_ROOT/AI-learned/donetoday.json"
    
    exit 0
else
    echo -e "${RED}✗ FEILET: Fant $FOUND_COUNT banned ord!${NC}"
    echo -e "${YELLOW}Fjern disse før du fortsetter til neste fase.${NC}"
    
    # Logg til donetoday.json
    TIMESTAMP=$(date -Iseconds)
    TEMP_FILE=$(mktemp)
    
    jq --arg ts "$TIMESTAMP" \
       --arg count "$FOUND_COUNT" \
       '.aktiviteter += [{
           "timestamp": $ts,
           "fase": "0.2",
           "beskrivelse": "Banned-ord sjekk FEILET",
           "filer": ["alle .php, .blade.php, .js, .vue filer"],
           "resultat": ("FEIL - fant " + $count + " banned ord")
       }]' "$PROJECT_ROOT/AI-learned/donetoday.json" > "$TEMP_FILE"
    
    mv "$TEMP_FILE" "$PROJECT_ROOT/AI-learned/donetoday.json"
    
    exit 1
fi
