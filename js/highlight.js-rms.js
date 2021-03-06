/*
Language: RMS
Requires: -
Author: HSZemi
Contributors: -
Description: Age of Empires 2 Random Map Scripting language
*/

function rmslanguage(hljs) {
    return {
        keywords: {
            keyword: 'if elseif else endif random_placement start_random end_random percent_chance',
            symbol: 'ai_info_map_type base_terrain cliff_curliness create_elevation create_land create_object create_player_lands create_terrain direct_placement effect_amount effect_percent grouped_by_team guard_state max_length_of_cliff max_number_of_cliffs min_distance_cliffs min_length_of_cliff min_number_of_cliffs min_terrain_distance nomad_resources random_placement reate_connect_all_players_land terrain_state weather_type',
            params: 'assign_to assign_to_player base_elevation base_size base_terrain border_fuzziness bottom_border clumping_factor default_terrain_replacement group_placement_radius group_variance height_limits land_id land_percent land_position left_border max_distance_to_other_zones max_distance_to_players min_distance_group_placement min_distance_to_players number_of_clumps number_of_groups number_of_objects number_of_tiles other_zone_avoidance_distance place_on_specific_land_id replace_terrain resource_delta right_border set_avoid_player_start_areas set_flat_terrain_only set_gaia_object_only set_loose_grouping set_place_for_every_player set_scale_by_groups set_scale_by_size set_scaling_to_map_size set_scaling_to_player_number set_tight_grouping set_zone_by_team set_zone_randomly spacing spacing_to_other_terrain_types temp_min_distance_group_placement terrain_cost terrain_size terrain_to_place_on terrain_type top_border type zone'
        },
        contains: [
            hljs.C_BLOCK_COMMENT_MODE,
            hljs.C_NUMBER_MODE,
            {
                className: 'title',
                begin: '<',
                end: '>',
            },
            {
                className: 'name',
                begin: '[A-Z][A-Z_0-9]*',
                relevance: 0
            },
            {
                className: 'meta',
                begin: /#(define|const|include_drs)/,
            }
        ]
    }
}

function normalizeMap(mapcontent) {
    mapcontent = mapcontent.replace(/#include_drs\s+random_map.def/g, "#include_drsrandom_mapdef");
    let commentMatches = mapcontent.match(/\n?\/\*.*?\*\//g);
    if (commentMatches !== null) {
        for (let i = 0; i < commentMatches.length; i++) {
            mapcontent = mapcontent.replace(commentMatches[i], `§comment§${i}§`);
        }
    }
    mapcontent = mapcontent.replace(/\r?\n/g, " ");
    mapcontent = mapcontent.replace(/\s+/g, " ");
    mapcontent = mapcontent.replace(/\s([a-z_]+)/g, "\n$1");
    mapcontent = mapcontent.replace(/#/g, "\n#");
    mapcontent = mapcontent.replace(/\{/g, "\n{");
    mapcontent = mapcontent.replace(/\}/g, "\n}\n");
    if (commentMatches !== null) {
        for (let i = 0; i < commentMatches.length; i++) {
            mapcontent = mapcontent.replace(`§comment§${i}§`, commentMatches[i]);
        }
    }
    mapcontent = mapcontent.replace(/\s+\*\//g, " */");
    mapcontent = mapcontent.replace(/ \n/g, "\n");
    mapcontent = mapcontent.replace(/#include_drsrandom_mapdef/g, "#include_drs random_map.def");
    mapcontent = mapcontent.replace(/\s+\n/g, "\n");
    mapcontent = mapcontent.replace(/</g, "\n\n<");
    mapcontent = mapcontent.replace(/\n\/\*/g, "\n\n/*");
    mapcontent = mapcontent.replace(/\*\/\n\s*\n\/\*/g, "*/\n/*");
    mapcontent = mapcontent.replace(/^\n+/g, "");

    let lines = mapcontent.split("\n");

    let mapLines = [];
    let indent = 0;
    let lineNr = 0;
    let lineInError = 0;
    let latestIndent = 0;
    for (let line of lines) {
        lineNr++;
        let indentBefore = getIndentBefore(line);
        let indentAfter = getIndentAfter(line);
        if (lineInError < 1 && indent + indentBefore < 0) {
            lineInError = lineNr;
        }
        mapLines.push(getIndent(indent + indentBefore) + line);
        indent += indentAfter;
        latestIndent = indent;
    }
    if (lineInError < 1 && latestIndent !== 0) {
        lineInError = lineNr;
    }

    if (lineInError > 0) {
        alert("The indentation revealed issues with your brackets or if/else statements. " +
            "The first indentation error was noticed on line: " + lineInError);
    }

    return mapLines.join("\n");
}

function getIndentBefore(line) {
    if (line.startsWith("elseif")) {
        return -1;
    }
    if (line.startsWith("else")) {
        return -1;
    }
    if (line.startsWith("endif")) {
        return -1;
    }
    if (line.startsWith("end_random")) {
        return -1;
    }
    if (line.startsWith("}")) {
        return -1;
    }
    return 0;
}

function getIndentAfter(line) {
    if (line.startsWith("if")) {
        return 1;
    }
    if (line.startsWith("endif")) {
        return -1;
    }
    if (line.startsWith("{")) {
        return 1;
    }
    if (line.startsWith("}")) {
        return -1;
    }
    if (line.startsWith("start_random")) {
        return 1;
    }
    if (line.startsWith("end_random")) {
        return -1;
    }
    return 0;
}

function getIndent(i) {
    //if (i < 0) { return "XXXXXX"; }
    return " ".repeat(Math.max(i * 4, 0));
}