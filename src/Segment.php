<?php


namespace JDWX\Args;

enum Segment {


    case DELIMITER;
    case UNQUOTED;
    case SINGLE_QUOTED;
    case DOUBLE_QUOTED;
    case BACK_QUOTED;
    case COMMENT;


}

